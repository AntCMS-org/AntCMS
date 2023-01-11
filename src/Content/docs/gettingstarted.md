--AntCMS--
Title: Getting Started
Author: The AntCMS Team
Description: Getting started with AntCMS.
--AntCMS--

# Getting Started with AntCMS

Due to it's simplistic nature, getting started with AntCMS is very easy compared to some CMS solutions. Simply follow the steps we have laid out below, and you'll be off to the races!

## Installing AntCMS

First, head over to our [GitHub repository](https://github.com/AntCMS-org/AntCMS/releases) and download the latest release.
At the moment, AntCMS is still under heavy development, so the only available release will be the preview build, which is automatically updated whenever we update something.

Once you've downloaded the latest release, follow these steps to install AntCMS on your webserver and get it up and running.

1. Ensure that your webserver is running at least PHP 8.0, although for best performance we recommend PHP 8.1 or newer.
2. If you are using nginx, you will need to download the nginx config from [here](https://raw.githubusercontent.com/AntCMS-org/AntCMS/main/configs/nginx.conf)
3. Copy the installation files to the `public_html` directory for your domain. Note: while it may be possible to use AntCMS under a sub directory, it's much more likely to have issues.
4. Access AntCMS from the web, by doing so you will cause AntCMS to generate it's initial configuration files. (ex: antcms.example.com)
5. Edit the `Config/config.yaml` file to specify the options specific to your website
   1. More in-depth descriptions on these options are available on our [readme](https://github.com/AntCMS-org/AntCMS#readme)
   2. You should at the very least set the `siteTitle` and the `password`. Note: setting the password is only required for you to access the admin plugin or anywhere else that may require authentication
   3. If you would like, you may also change the theme your site is using. We currently offer 'Default' and 'Bootstrap' themes, both of which are fast, pretty, and well optimized for SEO.

Congratulations! You have no completed the basic steps for setting up AntCMS on your website.

### Writing Content

Writing content for AntCMS is easy as it uses [markdown](https://www.markdownguide.org/cheat-sheet/). AntCMS supports most extended markdown syntax, including emojis and some GitHub styled markdown extras.

All content is stored in the `/Content` directory as `.md` files. Subfolders can be used. For example: `/Content/docs/gettingstarted.md` will be accessible by going to example.com/docs/gettingstarted.md

All pages must include a page header, this is used by AntCMS to get important page data. Please see this example for a page header:
```
--AntCMS--
Title: An Example!
Author: The AntCMS Team
Description: Getting started with AntCMS.
--AntCMS--
```

When creating your page header, be sure to put a space after the ':', omitting it will cause issues when AntCMS tries to fetch the header info.
Valid: `Title: This is a Title` invalid: `Title:This is a Title`.

When you create a new page, it won't be automatically added to the page navigation on your website. This is because of the way AntCMS generates a list of all pages and then returns that list, rather than re-discovering your pages on each request.
To manually add a new page, you can manually edit the `Config/pages.yaml` file, delete the file which will cause AntCMS to automatically regenerate it, or use the admin plugin to regenerate the list. (covered later in this guide)

Just as how a page is simply created by adding a new file to the `/Content` directory, deleting it is as easy as deleting the file and removing it from the `/Config/pages.yaml` file.

Note: In the future, the page management experience will be improved to provide greater flexibility and to be more streamlined.

#### The Admin Plugin

AntCMS has a basic admin plugin to make it easier to write content for your website. While the styling is limited, the plugin does provide features to help creating content a bit easier.
To login to the admin plugin, visit `example.com/plugin/admin`. You will then be prompted to login to login with the credentials you setup in your `Config/config.yaml` file.

The plugin provides a few easy tools, such as a way to edit the configuration file of your AntCMS instance, create a new page, or edit existing content.
The plugin also provides a live preview of the content you are writing. (note: the preview may support all of the markdown features the core app has.)
Here's a preview from the admin plugin:
![alt text](https://raw.githubusercontent.com/AntCMS-org/.github/main/screenshots/contentpreview.png)

And there you go! You should now have a fully functional instance of AntCMS, a fast, tiny, and simple CMS to get your content online!