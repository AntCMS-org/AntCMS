--AntCMS--
Title: Image Compression
Author: The AntCMS Team
Description: Learn about automated image compression functionally in AntCMS.
--AntCMS--


# Image Compression

AntCMS has built in support for automatically compressing images.
It will do this when you have the GD PHP extension installed and asset delivery is running through AntCMS rather than being handled by your webserver.

Images are compressed a single time with the compressed version being retained in AntCMS's cache. The original file remains untouched.

## Supported Image types

 - JPEG / JPG
 - PNG
 - WEBP

## Usage

Image compression is automatically enabled for all supported images when using AntCMS.
All images will be compressed using the quality level defined in your configuration file (85% by default).

### Specifying the quality level.

If you want to use a specific quality level on an image rather than using a broad default, you may do so by providing an "imageQuality" GET parameter.

Examples:

- **Very High** (95%): `/assets/exampleImage.jpg?imageQuality=veryhigh`
- **High** (80%): `/assets/exampleImage.jpg?imageQuality=high`
- **Medium** (65%): `/assets/exampleImage.jpg?imageQuality=medium`
- **Low** (25%): `/assets/exampleImage.jpg?imageQuality=low`
- **Very Low** (0%): `/assets/exampleImage.jpg?imageQuality=verylow`


## Default
![Default quality](/assets/exampleImage.jpg)

### Very High

![Very high quality preset](/assets/exampleImage.jpg?imageQuality=veryhigh)

### High

![High quality preset](/assets/exampleImage.jpg?imageQuality=high)

### Medium

![Medium quality preset](/assets/exampleImage.jpg?imageQuality=low)

### Low

![Low quality preset](/assets/exampleImage.jpg?imageQuality=verylow)

### Very Low

![Very low quality preset](/assets/exampleImage.jpg?imageQuality=verylow)
