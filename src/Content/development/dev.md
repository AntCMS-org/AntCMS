--AntCMS--
Title: Quick Start
Author: The AntCMS Team
Description: Learn how to quickly deploy a development version of AntCMS
NavItem: true
--AntCMS--

# Requirements

This tutorial will utilize the [DDEV](https://ddev.com/) PHP developer environment, so any system that meets their requirements should be sufficient here.

Fundamental requirements:

- Docker
- Git
- DDEV

---

## Setting Up

First, clone the AntCMS source code and move into the root directory.

```shell
git clone https://github.com/AntCMS-org/AntCMS.git
cd AntCMS
```

Next, you'll want to start the DDEV container for AntCMS.

```shell
ddev start
```

### And that's it!

Assuming you ran into no errors, you should now find your AntCMS developer instance available at [https://antcms.ddev.site](https://antcms.ddev.site).
