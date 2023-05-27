<?php
$cacheDir = __DIR__ . DIRECTORY_SEPARATOR . 'Cache' . DIRECTORY_SEPARATOR;
$di = new RecursiveDirectoryIterator($cacheDir, FilesystemIterator::SKIP_DOTS);
$ri = new RecursiveIteratorIterator($di, RecursiveIteratorIterator::CHILD_FIRST);
foreach ($ri as $file) {
  $file->isDir() ?  rmdir($file->getRealPath()) : unlink($file->getRealPath());
}

if (extension_loaded('apcu') && apcu_enabled()) {
  apcu_clear_cache();
}
