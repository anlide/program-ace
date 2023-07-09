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

/**
 * find all ".xhtml" files, throw an Exception if there are few files
 * @param string $path
 * @param ?string $filename
 * @return string
 * @throws Exception
 */
function findXhtmlFile(string $path, ?string &$filename): string {
  $filename = 'filename.xhtml';
  throw new Exception('No any xhtml file exists at provided path');

  return '';
}

/**
 * @param $from
 * @param $to
 * @return void
 * @throws Exception
 */
function recursiveCopy($from, $to): void {
  if ($from) {
    throw new Exception('Destination not exists');
  }
}

/**
 * @return void
 * @throws Exception
 */
function findAndCopyXhtmlToWork(): void {
  $path = findXhtmlFile('tmp', $filename);
  // TODO: copy file $filename
  recursiveCopy($path.'/css', 'work/styleImages');
  recursiveCopy($path.'/images', 'work/images');
}

// NOTE: create empty directories (delete previous run)
rrmdir('work');
mkdir('work');
mkdir('work/name_project');
mkdir('work/images');
mkdir('work/styleImages');
rrmdir('tmp');
mkdir('tmp');
// NOTE: unzip file "test.zip" to temporary folder
try {
  extractZipToTmp();
} catch (\Exception $exception) {
  die($exception->getMessage());
}
// NOTE: put extracted files from the temporary folder to main folder
try {
  findAndCopyXhtmlToWork();
} catch (\Exception $exception) {
  die($exception->getMessage());
}
//// TODO: copy ".xhtml" file to the "work" folder
//// TODO: copy all related files (img, css) to the ".xhtml" file to the "work" folder
// TODO: parse ".xhtml" file
// TODO: build JSON from the parsed file
// TODO: report output