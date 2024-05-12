--AntCMS--
Title: Compatible web servers
Author: The AntCMS Team
Description: Learn about what Markdown syntax and features AntCMS supports.
--AntCMS--

# Compatible Web Servers

<hr/>

## Apache, Litespeed, and OpenLitespeed

For all of the above web servers are compatible with `.htaccess` files and should automatically work correctly with AntCMS.

**Note:** OpenLitespeed will require you to reload the service before it reads the `.htaccess` file for AntCMS.

## NGINX

For NGINX we have a [nginx.conf](https://github.com/AntCMS-org/AntCMS/blob/main/configs/nginx.conf) template provided.
At the moment, this is not yet a tested & validated configuration.

## Caddy

AntCMS has been tested and validated to work when paired with Caddy.
The default Caddy behavior for PHP applications is compatible, however relying on this will leave you without functionality like asset and image compression.

For your convience, a pre-built [caddyfile](https://github.com/AntCMS-org/AntCMS/blob/main/configs/caddyfile) is available which will ensure all routing behaves as originally intended.

**Note:** ETags are broken in AntCMS when paired with Caddy and are automatically disabled. Due to this, using Caddy will result in reduced caching capabilities for assets.
