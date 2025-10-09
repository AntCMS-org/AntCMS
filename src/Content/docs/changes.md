--AntCMS--
Title: AntCMS Changelog
Author: The AntCMS Team
Description: AntCMS changelog - including migration steps when needed
--AntCMS--

# Migration Steps

Upgrading to a newer version of AntCMS?
This page will document any changes that will need custom migration, if any are needed.

## 0.5.0

### End-Users
 - No end-user changes.

### Developers
 - `getFileList` was removed from the tools class. AntCMS now bundles the symfony finder component which can be used instead.
