<?php

// https://gist.github.com/ckressibucher/e37520cf2f1d08ec56d250c54d96ed72

// public/cliserver.php (router script)

if (PHP_SAPI !== 'cli-server') {
  die('this is only for the php development server');
}

$file = (is_string($_SERVER['DOCUMENT_ROOT']) && is_string($_SERVER['SCRIPT_NAME'])) ? $_SERVER['DOCUMENT_ROOT'] . '/' . $_SERVER['SCRIPT_NAME'] : null;

if ($file !== null && is_file($file)) {
  // probably a static file...
  return false;
}

$_SERVER['SCRIPT_NAME'] = '/index.php';
// if needed, fix also 'PATH_INFO' and 'PHP_SELF' variables here...

// require the entry point
require __DIR__ . '/index.php';
