<?php
const PATH_TMP = 'tmp';
const PATH_WORK = 'work';
const PATH_WORK_XHTML = 'work/name_project';
const PATH_WORK_IMG = 'work/images';
const PATH_WORK_CSS = 'work/styleImages';

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
 * @return string
 * @throws Exception
 */
function findXhtmlFile(string $path): string {
  $filename = null;
  $dir = new RecursiveDirectoryIterator($path);
  foreach (new RecursiveIteratorIterator($dir) as $fullName => $file) {
    if (str_contains($file->getFilename(), 'xhtml')) {
      if ($filename !== null) {
        throw new Exception('There are few xhtml in the zip');
      }
      $filename = $fullName;
    }
  }
  if ($filename === null) {
    throw new Exception('No any xhtml file exists at provided path');
  }

  return $filename;
}

/**
 * @param $from
 * @param $to
 * @return void
 * @throws Exception
 */
function recursiveCopy($from, $to): void {
  if (!is_dir($from)) {
    throw new Exception('Source not exists');
  }
  if (!is_dir($to)) {
    throw new Exception('Destination not exists');
  }

  foreach (
    $iterator = new \RecursiveIteratorIterator(
      new \RecursiveDirectoryIterator($from, \RecursiveDirectoryIterator::SKIP_DOTS),
      \RecursiveIteratorIterator::SELF_FIRST) as $item
  ) {
    if ($item->isDir()) {
      mkdir($to . '/' . $iterator->getSubPathname());
    } else {
      copy($item, $to . '/' . $iterator->getSubPathname());
    }
  }
}

/**
 * @return void
 * @throws Exception
 */
function findAndCopyXhtmlToWork(): void {
  $filename = findXhtmlFile(PATH_TMP);
  if (!copy($filename, PATH_WORK_XHTML . '/' . pathinfo($filename)['basename'])) {
    throw new Exception('Unable to copy file: ' . $filename);
  }
  $path = pathinfo($filename)['dirname'];
  recursiveCopy($path . '/images', PATH_WORK_IMG);
  recursiveCopy($path . '/css', PATH_WORK_CSS);
}

// NOTE: create empty directories (delete previous run)
rrmdir(PATH_WORK);
mkdir(PATH_WORK);
mkdir(PATH_WORK_XHTML);
mkdir(PATH_WORK_IMG);
mkdir(PATH_WORK_CSS);
rrmdir(PATH_TMP);
mkdir(PATH_TMP);
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