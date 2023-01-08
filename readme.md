# AntCMS

[![PHPStan](https://github.com/BelleNottelling/AntCMS/actions/workflows/phpstan.yml/badge.svg)](https://github.com/BelleNottelling/AntCMS/actions/workflows/phpstan.yml)

A tiny and fast CMS system for static websites.

## What is AntCMS?

AntCMS is a lightweight CMS system designed for simplicity, speed, and small size. It is a flat-file CMS, meaning it lacks advanced features but benefits from improved speed and reduced complexity.

### How fast is AntCMS?

AntCMS is extremely fast, thanks to its simple backend and caching. It can render and deliver pages to end users in milliseconds.
This speed is made even faster by the fact that our default theme is created using Tailwind and is only 20Kb!

### How does it work?

AntCMS is very straightforward to use. First, you need a template in HTML with special elements for AntCMS. Then, you write your content using [markdown](https://www.markdownguide.org/getting-started/), a popular way to format plain text documents. AntCMS converts the markdown to HTML, integrates it into the template, and sends it to the viewer. Even without caching, this process is quick, but AntCMS also has caching capabilities to further improve rendering times.

### Theming with AntCMS

AntCMS stores its themes under `/Themes`. Each theme is extremely simple, just a simple page layout template.
A theme may also have a `/Themes/Example/Assets` folder, these files can be accessed directly from the server. Files stored in any other location will be inaccessible otherwise.
For example, this is what the default theme folder structure looks like:

- `/Themes`
  - `/Default`
    - `/Templates`
      - `default_layout.html`
      - `nav_layout.html`
    - `/Assets`
      - `tailwind.css`

Changing the theme is easy, simply edit `config.yaml` and set the `activeTheme` to match the folder name of your custom theme.

### Configuring AntCMS

AntCMS stores it's configuration in the human readable `yaml` file format. The two files are `config.yaml` and `pages.yaml`.
Both files will automatically be generated by AntCMS if they don't exist.

#### Options in `config.yaml`

- `SiteInfo:`
  - `siteTitle: AntCMS` - This configuration sets the title of your AntCMS website.
- `forceHTTPS: true` - Set to 'true' by default, enables HTTPs redirection.
- `activeTheme: Default` - Sets what theme AntCMS should use. should match the folder name of the theme you want to use.
- `generateKeywords: true` - AntCMS will automatically attempt to generate keywords for each page if they don't have keywords defined. Set this to `false` to disable this. Note: this feature is very limited, we highly suggest manually creating keywords for your content.
- `enableCache: true` - Enables or disables file caching in AntCMS.
- `admin:`
  - `username: 'Admin'` - The username used to access any parts of AntCMS that may require authentication.
  - `password: 'dontmakeitpassword123'` - The password associated with the admin account. Can be entered as plain text and then will automatically be hashed with the first login. This does need to be manually entered initially.
- `debug: true`- Enabled or disables debug mode.
- `baseURL: antcms.example.com/` - Used to set the baseURL for your AntCMS instance, without the protocol. This will be automatically generated for you, but can be changed if needed.

#### Options in `pages.yml`

The `pages.yaml` file holds a list of your pages. This file is automatically generated if it doesn't exist. At the moment, AntCMS doesn't automatically regenerate this for you, so for new content to appear you will need to delete the `pages.yaml` file.
Here's what the `pages.yaml` file looks like:

- `pageTitle: 'Hello World'` - This defines what the title of the page is in the navbar.
- `fullPagePath: /antcms.example.com/public_html/Content/index.md` - This defines the full path to your page, as PHP would use to access it.
- `functionalPagePath: /index.md` - This is the actual path you would use to access the page from online. Ex: `antcms.example.com/index.php`
- `showInNav: true` - If you'd like to hide a page from the navbar, set this to false and it will be hidden.

#### The Admin Plugin

AntCMS has a very simple admin plugin. Once you set your password in your `config.yaml`, you can access it by visiting `antcms.example.com/plugin/admin`.
It will then require you to authenticate using your AntCMS credentials and from there will give you a few simple actions such as editing your config, a page, or regenerating the page list.

Note: when editing the config, if you 'save' it and it didn't update, this means you made an error in the config file and AntCMS prevented the file from being saved.
