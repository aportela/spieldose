<?php

    declare(strict_types=1);

    require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

    echo "Spieldose installer" . PHP_EOL;

    $settings = require dirname(__DIR__) . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "AppSettings.php";

    $app = (new \Spieldose\App($settings))->get();

    new \Spieldose\Installer();

?>