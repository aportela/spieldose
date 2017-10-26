<?php

    declare(strict_types=1);

    ob_start();

    if (PHP_SAPI == 'cli-server') {
        // To help the built-in PHP dev server, check if the request was actually for
        // something which should probably be served as a static file
        $url  = parse_url($_SERVER['REQUEST_URI']);
        $file = __DIR__ . $url['path'];
        if (is_file($file)) {
            return false;
        }
    }

    $settings = require __DIR__ . '/../src/configuration.php';

    require __DIR__ . '/../vendor/autoload.php';

    session_start();

    $settings = require __DIR__ . '/../src/AppSettings.php';

    $app = (new \Spieldose\App($settings))->get();

    require __DIR__ . '/../src/AppDependencies.php';
    require __DIR__ . '/../src/AppRoutes.php';

    $app->run();

?>