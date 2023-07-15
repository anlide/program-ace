<?php
const PATH_TMP = 'tmp';
const PATH_WORK = 'work';
const PATH_WORK_XHTML = 'work/name_project';
const PATH_WORK_IMG = 'work/images';
const PATH_WORK_CSS = 'work/styleImages';

const JSON_BLOCKS = 'blocks';
const JSON_BLOCKS_BLOCKID = 'blockId';
const JSON_BLOCKS_HTML = 'html';
const JSON_IMAGES = 'images';
const JSON_IMAGES_IMAGEID = 'imageId';
const JSON_IMAGES_PATH = 'path';
const JSON_IMAGES_CAPTION = 'caption';
const JSON_IMAGES_CAPTION_LIMIT = 3000;
const JSON_TABLES = 'tables';
const JSON_TABLES_TABLEID = 'tableId';
const JSON_TABLES_HTML = 'html';
const JSON_TABLES_CAPTION = 'caption';

require "vendor/autoload.php";
use PHPHtmlParser\Dom;
use PHPHtmlParser\Exceptions\ParentNotFoundException;

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
 * @return string
 * @throws Exception
 */
function findAndCopyXhtmlToWork(): string {
  $filename = findXhtmlFile(PATH_TMP);
  $destinationFilename = PATH_WORK_XHTML . '/' . pathinfo($filename)['basename'];
  if (!copy($filename, $destinationFilename)) {
    throw new Exception('Unable to copy file: ' . $filename);
  }
  $path = pathinfo($filename)['dirname'];
  recursiveCopy($path . '/images', PATH_WORK_IMG);
  recursiveCopy($path . '/css', PATH_WORK_CSS);

  return $destinationFilename;
}

/**
 * @param string $filename
 * @return Dom
 * @throws \PHPHtmlParser\Exceptions\ChildNotFoundException
 * @throws \PHPHtmlParser\Exceptions\CircularException
 * @throws \PHPHtmlParser\Exceptions\ContentLengthException
 * @throws \PHPHtmlParser\Exceptions\LogicalException
 * @throws \PHPHtmlParser\Exceptions\StrictException
 */
function getDomFromXhtmlFile(string $filename): Dom {
  return @(new Dom)->loadFromFile($filename);
}

function parseDOMtoJson(Dom $dom): array {
  $json = [];
  // TODO: Make blocks part
  $json[JSON_BLOCKS] = [];
  $json[JSON_BLOCKS][JSON_BLOCKS_BLOCKID] = uniqid(JSON_BLOCKS, true);
  $json[JSON_BLOCKS][JSON_BLOCKS_HTML] = '';
  // NOTE: Make images part
  $json[JSON_IMAGES] = [];
  $images = $dom->find('img');
  foreach ($images as $image) {
    $jsonImage = [JSON_IMAGES_IMAGEID => uniqid(JSON_IMAGES, true)];
    $jsonImage[JSON_IMAGES_PATH] = $image->getAttribute('src');
    $jsonImage[JSON_IMAGES_CAPTION] = $image->getAttribute('alt');;
    try {
      $figure = $image->ancestorByTag('figure');
      $figcaptions = $figure->find('figcaption');
      if (count($figcaptions) !== 1) {
        throw new Exception('Wrong format figcaption');
      }
      $jsonImage[JSON_IMAGES_CAPTION] = $figcaptions[0]->innerHtml();
    } catch (ParentNotFoundException|Exception $exception) {
      continue;
    } finally {
      $jsonImage[JSON_IMAGES_CAPTION] = substr($jsonImage[JSON_IMAGES_CAPTION], 0, JSON_IMAGES_CAPTION_LIMIT);
      $json[JSON_IMAGES][] = $jsonImage;
    }
  }
  // NOTE: Make tables part
  $json[JSON_TABLES] = [];
  $tables = $dom->find('table');
  foreach ($tables as $table) {
    $jsonTable = [JSON_TABLES_TABLEID => uniqid(JSON_TABLES, true)];
    $jsonTable[JSON_TABLES][JSON_TABLES_CAPTION] = '';
    $jsonTable[JSON_TABLES][JSON_TABLES_HTML] = '';
    $json[JSON_TABLES][] = $jsonTable;
  }

  return $json;
}

try {
  // NOTE: create empty directories (delete previous run)
  rrmdir(PATH_WORK);
  mkdir(PATH_WORK);
  mkdir(PATH_WORK_XHTML);
  mkdir(PATH_WORK_IMG);
  mkdir(PATH_WORK_CSS);
  rrmdir(PATH_TMP);
  mkdir(PATH_TMP);
  // NOTE: unzip file "test.zip" to temporary folder
  extractZipToTmp();
  // NOTE: put extracted files from the temporary folder to main folder
  $filename = findAndCopyXhtmlToWork();
  // NOTE: parse ".xhtml" file
  $dom = getDomFromXhtmlFile($filename);
  // NOTE: build JSON from the parsed file
  $json = parseDOMtoJson($dom);
  // NOTE: report JSON file
  var_dump($json);
} catch (\Exception $exception) {
  die($exception->getMessage());
}
