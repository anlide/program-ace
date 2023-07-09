<?php
function rrmdir($dir) {
  if (is_dir($dir)) {
    $objects = scandir($dir);
    foreach ($objects as $object) {
      if ($object != "." && $object != "..") {
        if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object))
          rrmdir($dir. DIRECTORY_SEPARATOR .$object);
        else
          unlink($dir. DIRECTORY_SEPARATOR .$object);
      }
    }
    rmdir($dir);
  }
}

// NOTE: create empty directories (delete previous run)
rrmdir('work');
mkdir('work');
rrmdir('tmp');
mkdir('tmp');
// TODO: unzip file "test.zip" to temporary folder
// TODO: put extracted files from the temporary folder to main folder
// TODO: parse ".xhtml" file
// TODO: build JSON from the parsed file
// TODO: report output