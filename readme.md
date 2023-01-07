# AntCMS

[![PHPStan](https://github.com/BelleNottelling/AntCMS/actions/workflows/phpstan.yml/badge.svg)](https://github.com/BelleNottelling/AntCMS/actions/workflows/phpstan.yml)

A tiny and fast CMS system for static websites.

## What is AntCMS

AntCMS is a lightweight CMS system designed for simplicity, speed, and small size. It is a flat file CMS, meaning it lacks advanced features but benefits from improved speed and reduced complexity.

### How fast is AntCMS?

AntCMS is extremely fast, thanks to its simple backend and caching. It can render and deliver pages to end users in milliseconds.

### How does it work?

AntCMS is very straightforward to use. First, you need a template in HTML with special elements for AntCMS. Then, you write your content using [markdown](https://www.markdownguide.org/getting-started/), a popular way to format plain text documents. AntCMS converts the markdown to HTML, integrates it into the template, and sends it to the viewer. Even without caching, this process is quick, but AntCMS also has caching capabilities to further improve rendering times.

### Themeing with AntCMS

AntCMS stores it's themes under `/Themes`. Each theme is extremely simple, just a simple page layout template and if needed, assets associated with that theme.
A theme may also have a `/Themes/Example/Assets` folder, these files can be accessed directly from the server. Files stored in any other location will be inaccessible otherwise.
For example, this is what the default theme folder structure looks like:

- `/Themes`
  - `/Default`
    - `default_layout.html`

Changing the theme is easy, simply edit `Config.yaml` and set the `activeTheme` to match the folder name of your custom theme.
