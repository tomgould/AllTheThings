<?php

/**
 * Zips all the things and buffers the zip to your browser
 *
 * @param string $source
 *   The dir to zip
 * @param type $destination
 *   The temporary writable location eg "/tmp/ALLTHETHINGS.zip"
 * 
 */
function all_the_things($source, $destination) {
  ini_set('memory_limit', -1);
  if (!extension_loaded('zip') || !file_exists($source)) {
    return false;
  }

  $zip = new ZipArchive();
  if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
    return false;
  }

  $source = str_replace('\\', '/', realpath($source));

  if (is_dir($source) === true) {
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

    foreach ($files as $file) {
      $file = str_replace('\\', '/', $file);

      // Ignore "." and ".." folders
      if (in_array(substr($file, strrpos($file, '/') + 1), array('.', '..')))
        continue;

      $file = realpath($file);

      if (is_dir($file) === true) {
        $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
      }
      else if (is_file($file) === true) {
        $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
      }
    }
  }
  else if (is_file($source) === true) {
    $zip->addFromString(basename($source), file_get_contents($source));
  }

  $zip->close();

  header("Content-type: application/zip");
  header("Content-Disposition: attachment; filename=" . basename($destination) . '.zip');
  header("Content-length: " . filesize($destination));
  header("Pragma: no-cache");
  header("Expires: 0");
  readfile("$destination");

  exit;

}

all_the_things('/var/www/sitename/docroot/', '/tmp/ALLTHETHINGS.zip');

