<?php

$file_to_zip    = '/home/tgould/www/ines/docroot/sites/sites.php';
$zip_file_name = '/tmp/ZIPATHING.zip';

$zip = new ZipArchive();
if (!$zip->open($zip_file_name, ZIPARCHIVE::CREATE)) {
  return false;
}

$zip->addFromString(basename($file_to_zip), file_get_contents($file_to_zip));
$zip->close();

header("Content-type: application/zip");
header("Content-Disposition: attachment; filename=" . basename($zip_file_name));
header("Content-length: " . filesize($zip_file_name));
header("Pragma: no-cache");
header("Expires: 0");
readfile("$zip_file_name");
exit;