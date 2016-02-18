<?php
/**
 * @file
 *     Zips up the $dir_to_zip andcreates a zip at $zip_file_name,
 *     sends it to the browser and deletes the zip from the server
 */
// Replace these values
$dir_to_zip    = '/home/tgould/www/ines/';
$zip_file_name = '/tmp/ALLTHETHINGS.zip';

// Paths to exclude form the zip
$excludes = array(
  'docroot/sites/',
);

all_the_things($dir_to_zip, $zip_file_name);
unlink($zip_file_name);

/**
 * Zips all the things and buffers the zip to your browser
 *
 * @param string $dir_to_zip
 *   The dir to zip
 * @param type $zip_file_name
 *   The temporary writable location eg "/tmp/ALLTHETHINGS.zip"
 * 
 */
function all_the_things($dir_to_zip, $zip_file_name) {
  ini_set('memory_limit', -1);
  ini_set('output_buffering', 'On');
  set_time_limit(600);

  if (!extension_loaded('zip') || !file_exists($dir_to_zip)) {
    return false;
  }

  $zip = new ZipArchive();
  if (!$zip->open($zip_file_name, ZIPARCHIVE::CREATE)) {
    return false;
  }

  $dir_to_zip = str_replace('\\', '/', realpath($dir_to_zip));

  if (is_dir($dir_to_zip) === true) {
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir_to_zip), RecursiveIteratorIterator::SELF_FIRST);

    foreach ($files as $file) {
      $file = str_replace('\\', '/', $file);

      foreach ($excludes as $exclude) {
        if (strpos($file, $exclude) !== FALSE) {
          continue 2;
        }
      }
      
      // Ignore "." and ".." folders
      if (in_array(substr($file, strrpos($file, '/') + 1), array('.', '..')))
        continue;

      $file = realpath($file);

      if (is_dir($file) === true) {
        $zip->addEmptyDir(str_replace($dir_to_zip . '/', '', $file . '/'));
      }
      else if (is_file($file) === true) {
        $zip->addFromString(str_replace($dir_to_zip . '/', '', $file), file_get_contents($file));
      }
    }
  }
  else if (is_file($dir_to_zip) === true) {
    $zip->addFromString(basename($dir_to_zip), file_get_contents($dir_to_zip));
  }

  $zip->close();

  header("Content-type: application/zip");
  header("Content-Disposition: attachment; filename=" . basename($zip_file_name));
  header("Content-length: " . filesize($zip_file_name));
  header("Pragma: no-cache");
  header("Expires: 0");
  readfile("$zip_file_name");

  exit;

}
