# AGENTS.md

Flat-file CMS in PHP (FlightPHP routing, Twig templates, league/commonmark markdown). PHP >= 8.2; CI runs PHPUnit on PHP 8.2–8.5, PHPStan level 6 on 8.2.

## Setup

- Composer installs into `src/Vendor` (composer.json sets `vendor-dir`), NOT `vendor/`. Dev binaries live at `src/Vendor/bin/`.
- Frontend assets must be built before the default theme works: `npm ci && npm run build` (esbuild JS; sass + lightningcss CSS). Output goes to the gitignored `src/Themes/Default/Assets/build/`.
- Preferred dev env is DDEV (`ddev start`). `.ddev/post-start.sh` auto-installs composer/npm deps and builds the frontend. Docroot is `src`; app served at https://antcms.ddev.site.
- APCu is optional but strongly recommended (app cache and AntLoader). CI enables it.
- First run auto-generates `src/Config/Config.yaml`. `src/Config/Pages.yaml`, `src/Cache/*`, and `src/Content/*` (except `index.md`) are generated/gitignored; `src/Plugins/Example` and `src/Themes/Default/Assets/build` are gitignored too.
- PHP extensions required: `curl`, `dom`, `mbstring`. Suggested extras: `zstd`, `brotli`, `zlib`, `gd`, `opcache`, `ctype`.

## Commands

- Run tests: `php src/Vendor/bin/phpunit` (or `composer test`). Focus one file with `php src/Vendor/bin/phpunit tests/PagesTest.php`, or one test with `--filter SomeTest`.
- Static analysis: `php src/Vendor/bin/phpstan` (level 6, config `phpstan.neon`, bootstraps `src/bootstrap.php`).
- Style: `composer run-cs` (rector + php-cs-fixer, applies fixes); `composer cs-check` (dry-run). Single file: `php src/Vendor/bin/php-cs-fixer fix <path>`.
- `composer stan` runs `php src/cli.php clearCache` then PHPStan.
- AntCMS CLI: `php src/cli.php <command>` (in DDEV: `ddev antcli <command>`). Built-ins: `clearCache`, `listHooks`, `runCron`, `initExample`, `precompute`. Commands are discovered from `src/Plugins/*/Commands/*.php`; the class name must equal the filename.

## Workflow

- After adding/renaming a class or plugin, run clearCache: `php src/cli.php clearCache` (or `ddev antcli clearCache`). AntLoader caches a class map at `src/Cache/classMap.php` (or APCu) for ~1 week, so new classes won't autoload until it's cleared.
- Before considering work done: `composer run-cs` -> `composer stan` -> `php src/Vendor/bin/phpunit`.

## Architecture

- Entry points: `src/index.php` (HTTP) and `src/cli.php` (CLI). Both load `src/bootstrap.php`, which defines the `PATH_*` constants, registers AntLoader namespaces (`AntCMS\` -> `src/AntCMS`, `AntCMS\Plugins\` -> `src/Plugins`) with class map at `src/Cache/classMap.php`, generates the config file if missing, defines `COMPRESS_*`, `BASE_URL`, `DEBUG_LEVEL`, `CURRENT_THEME`, `PATH_CURRENT_THEME`, and `IMAGE_QUALITY`, then sets up the cache adapter and Twig environment.
- Request flow: Flight routes are registered in `src/index.php` — asset routes (`GET /themes/*/assets/*`, `GET /assets/*`), favicon/ACME routes, the `/api/v0` route group, plugin routes, then catch-all `GET /*` -> `AntCMS->renderPage()`. Page content comes from markdown files in `src/Content` using the `--AntCMS--` front-matter delimiter (with `Title`, `Author`, `Description`, `Keywords`, `Template`, and `NavItem` headers), is run through league/commonmark, and rendered via the theme's Twig templates.
- Plugins: one directory per plugin under `src/Plugins/<Name>/` with a `Controller` class extending `AntCMS\AbstractPlugin` (constructor registers routes/hooks). Optional `Templates/` is auto-added to the Twig loader; optional `Api/PublicApi.php` is exposed at `/api/v0/public/<plugin>/<method>`. The built-in `src/Plugins/System/Controller.php` registers the canonical hook list plus the debug callback, and skips plugins without a Controller.
- Hooks: `AntCMS\HookController::registerHook|registerCallback|fire`; callbacks receive and return an `AntCMS\Event`. Built-ins: `onAfterContentHit`, `onAfterPerformanceMetricsBuilt`, `onBeforeApiCalled`, `onAfterApiCalled`, `onHookFireComplete`, `onBeforeMarkdownParsed`, `onAfterMarkdownParsed`, `onAfterPluginsInit`, `onBeforeOutputFlushed`, `onBeforeCronRun`, `onAfterCronRun`, `onAfterCacheHit`.
- Debug output: the `<!--AntCMS-Debug-->` marker in the response body is replaced with `AntCMS\Tools::buildDebugInfo()` via the `onBeforeOutputFlushed` hook.

## Conventions and gotchas

- PHP-CS-Fixer enforces a `/** Copyright 2026 AntCMS */` header (PHPDoc), `@PER-CS`, ordered imports, and single-line `//` comments only. Add the header to new files or lint will rewrite them.
- PHPStan (level 6) and PHPUnit skip `src/Vendor` and `src/Cache`. PHPUnit source coverage additionally excludes `src/index.php`, `src/cli.php`, `src/bootstrap.php`, and `src/AntCMS/Environment.php`.
- Tests bootstrap via `tests/Bootstrap.php`, which loads `src/bootstrap.php` and calls `AntCMS\PluginController::init()` so hook tests can run.
- Work targets `main`. CI runs PHPUnit (8.2–8.5 matrix), PHPStan (8.2), coding standards (`composer validate --strict` + `composer cs-check`), and frontend build; a rolling `latest-preview` release is built on every push to `main`. Dependabot runs daily for composer, npm, and GitHub Actions.
