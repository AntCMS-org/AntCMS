<?php
$cacheFiles = glob(__DIR__ . DIRECTORY_SEPARATOR . 'Cache' . DIRECTORY_SEPARATOR . '*.*');
foreach($cacheFiles as $cacheFile){
  if(is_file($cacheFile)) {
    unlink($cacheFile);
  }
}
?>
