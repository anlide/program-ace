<?php
/**
 * @param $dir
 * @return void
 */
function rrmdir($dir): void {
  if (is_dir($dir)) {
    $objects = scandir($dir);
    foreach ($objects as $object) {
      if ($object != "." && $object != "..") {
        if (is_dir($dir . DIRECTORY_SEPARATOR . $object) && !is_link($dir . "/" . $object))
          rrmdir($dir . DIRECTORY_SEPARATOR . $object);
        else
          unlink($dir . DIRECTORY_SEPARATOR . $object);
      }
    }
    rmdir($dir);
  }
}

/**
 * @throws Exception
 */
function extractZipToTmp(): void {
  $zip = new ZipArchive;
  $res = $zip->open('test.zip');
  if ($res === true) {
    $zip->extractTo('tmp');
    $zip->close();
  } else {
    throw new \Exception('unable to open zip');
  }
}

// NOTE: create empty directories (delete previous run)
rrmdir('work');
mkdir('work');
rrmdir('tmp');
mkdir('tmp');
// NOTE: unzip file "test.zip" to temporary folder
try {
  extractZipToTmp();
} catch (\Exception $exception) {
  die($exception->getMessage());
}
// TODO: put extracted files from the temporary folder to main folder
//// TODO: find all ".xhtml" files, throw an Exception if there are few files
//// TODO: copy ".xhtml" file to the "work" folder
//// TODO: copy all related files (img, css) to the ".xhtml" file to the "work" folder
// TODO: parse ".xhtml" file
// TODO: build JSON from the parsed file
// TODO: report output