<?php

    // credits: https://gist.github.com/ckressibucher/e37520cf2f1d08ec56d250c54d96ed72

    if (php_sapi_name() !== 'cli-server') {
        die('this is only for the php development server');
    }
    if (is_file($_SERVER['DOCUMENT_ROOT'] . '/' . $_SERVER['SCRIPT_NAME'])) {
        // probably a static file...
        return false;
    }
    $_SERVER['SCRIPT_NAME'] = '/index.php';
    // if needed, fix also 'PATH_INFO' and 'PHP_SELF' variables here...
    // require the entry point

    require 'index.php';

?>