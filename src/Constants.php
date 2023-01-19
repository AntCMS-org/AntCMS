<?php
const AntDir = __DIR__;
const AntCachePath   = __DIR__ . DIRECTORY_SEPARATOR . 'Cache';
const antConfigFile  = __DIR__ . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'config.yaml';
const antPagesList   = __DIR__ . DIRECTORY_SEPARATOR . 'Config' . DIRECTORY_SEPARATOR . 'pages.yaml';
const antContentPath = __DIR__ . DIRECTORY_SEPARATOR . 'Content';
const antThemePath   = __DIR__ . DIRECTORY_SEPARATOR . 'Themes';
const antPluginPath  = __DIR__ . DIRECTORY_SEPARATOR . 'Plugins';

if (in_array('xxh128', hash_algos())) {
    define('HAS_XXH128', true);
}
