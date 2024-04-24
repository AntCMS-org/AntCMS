<?php

require __DIR__ . '/Bootstrap.php';

$antCache = new \AntCMS\Cache();
$antCache->prune();
