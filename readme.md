<div align="center">

# 🐜 AntCMS

[![GitHub Downloads](https://img.shields.io/github/downloads/AntCMS-Org/AntCMS/total)](https://github.com/AntCMS-org/AntCMS/releases)
[![CI Status](https://img.shields.io/github/actions/workflow/status/AntCMS-Org/AntCMS/ci.yml)](https://github.com/AntCMS-org/AntCMS/actions/workflows/ci.yml)
[![License](https://img.shields.io/github/license/AntCMS-Org/AntCMS)](https://github.com/AntCMS-org/AntCMS/blob/main/LICENCE.md)
[![Stars](https://img.shields.io/github/stars/AntCMS-Org/AntCMS)](https://github.com/AntCMS-org/AntCMS/stargazers)
![Supported PHP Versions](https://img.shields.io/badge/PHP%20Versions-8.4%7C8.5%7C8.6-brightgreen)

**A super lightweight, flat-file CMS built for the modern web.**

[Website](https://antcms.org/) · [Documentation](https://antcms.org/docs/gettingstarted) · [Report an Issue](https://github.com/AntCMS-org/AntCMS/issues)

</div>

---

## ✨ Highlights

- ⚡ **Blazing Fast** — Website speeds rivaling a static site, zero manual tuning required
- 🪶 **Featherweight** — Extremely low resource usage, runs on modest hardware
- 🛠️ **Zero Config** — Built-in compression (gzip, brotli, zstd), asset optimization, and SEO handling
- 🎨 **Markdown + YAML** — Simple content workflow with front-matter headers
- 🧩 **Plugin & Theme System** — Extensible architecture with a growing ecosystem
- 🔧 **Developer Friendly** — PHPStan, PHPUnit, Rector, and modern tooling built in

> Full feature list on the [website](https://antcms.org/).

## 💻 Requirements

| | Requirement |
|---|---|
| **Required** | PHP 8.4+ with `curl`, `dom`, `mbstring` |
| **Recommended** | `zstd`, `brotli`, `opcache`, `APCu` |
| **Database** | None — flat-file architecture |

See the [documentation](https://antcms.org/docs/gettingstarted) for full setup details.

## 🚀 Quick Start

```bash
git clone https://github.com/AntCMS-org/AntCMS.git
cd AntCMS
composer install && npm ci && npm run build
```

For development with DDEV or production deployment, see the [Development Quick Start](https://antcms.org/development/dev).

## 📦 Tech Stack

- **Backend:** PHP 8.4+, [FlightPHP](https://flightphp.com/), Twig templating
- **Content:** Markdown via [league/commonmark](https://commonmark.thephpleague.com/)
- **Assets:** esbuild (JS), Sass + Lightning CSS (CSS)
- **Testing:** PHPUnit, PHPStan (level 6), Rector, PHP-CS-Fixer

## ⚠️ Project Status

AntCMS is **preview software** and is being actively rebuilt. The following functionality is currently missing:

- ❌ Administrator plugin (UI for system management)
- ❌ User management

## 🤝 Contributing

Contributions are welcome! Open an [issue](https://github.com/AntCMS-org/AntCMS/issues) or submit a [pull request](https://github.com/AntCMS-org/AntCMS/pulls).

See [AGENTS.md](AGENTS.md) for development setup and workflow details.

## 📄 License

Licensed under the [Apache License 2.0](LICENCE.md).

---

<div align="center">

**Built with ❤️ by the AntCMS community**

[![Website](https://img.shields.io/badge/Website-antcms.org-blue)](https://antcms.org/)
[![Documentation](https://img.shields.io/badge/Documentation-Docs-green)](https://antcms.org/docs/gettingstarted)
[![Plugins & Themes](https://img.shields.io/badge/Plugins--%26-Themes-Explore-orange)](https://antcms.org/plugins)
[![Changelog](https://img.shields.io/badge/Changelog-View%20Logs-lightgrey)](https://antcms.org/changelog)

</div>
