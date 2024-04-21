# AntCMS

![PHPStan Level](https://img.shields.io/badge/PHPStan-level%205-brightgreen)

AntCMS is being largely rebuilt for better, faser, easier functionality.

## Differences between "new" and "main"

- The main app now uses Flight for routing
- Optimizations
- Much improved code stylining and code quality
- Plugins do not yet work as they need to be rebuilt
- AntCMS will automatically perform brotli, zstd, and gzip output compression as long as related extensions are installed
- The vendor folder is now cleaned up to help reduce release size.
- Slightly improved debug info
- AntCMS now uses an actual twig loader
