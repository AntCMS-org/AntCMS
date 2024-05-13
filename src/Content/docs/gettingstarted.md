--AntCMS--
Title: Getting Started
Author: The AntCMS Team
Description: Getting started with AntCMS.
--AntCMS--

# Getting Started with AntCMS

Due to it's simplistic nature, getting started with AntCMS is very easy compared to some CMS solutions. Simply follow the steps we have laid out below, and you'll be off to the races!

---

## Installing AntCMS

**Note:** AntCMS is still under development and is considered alpha software due to not yet being complete, however it is already very fast and should be stable once setup.

1. Ensure you have PHP 8.0 or greater with the `curl`, `dom`, and `mbstring` extensions loaded.
2. Download the [latest release](https://github.com/AntCMS-org/AntCMS/releases) of AntCMS and extract it to your server.
3. If needed, install the [correct configuration](https://github.com/AntCMS-org/AntCMS/blob/main/configs) for your web server.
4. Access your domain. At this point, AntCMS will automatically create it's configuration file.
5. You're done! Time to move on to writing content.

### Writing Content

Writing content for AntCMS is easy as it uses [markdown](https://www.markdownguide.org/cheat-sheet/). AntCMS supports most extended markdown syntax, including emojis and some GitHub styled markdown extras.

All content is stored in the `/Content` directory as `.md` files. Subfolders can be used. For example: `/Content/docs/gettingstarted.md` will be accessible by going to example.com/docs/gettingstarted

All pages must include a page header, this is used by AntCMS to get important page data. Please see this example for a page header:

```yaml
--AntCMS--
Title: An Example!
Author: The AntCMS Team
Description: Getting started with AntCMS.
--AntCMS--
```

When creating your page header, be sure to put a space after the ':', omitting it will cause issues when AntCMS tries to fetch the header info.
Valid: `Title: This is a Title` invalid: `Title:This is a Title`.

If you find you need to re-order items in the navbar or rename a navbar drop down, you may do so by creating a `meta.yaml` file inside the directory.
Below are example contents for one of these files:
```yaml
# Sets the title of the dropdown menu
title: 'Documentation'

# Overrides the automatically selected order
pageOrder:
  gettingstarted: 1
  webservers: 2
  markdown: 3
```