<?php
    declare(strict_types=1);

    require __DIR__ . '/../vendor/autoload.php';

    require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR. "configuration.php";

    \Spieldose\Utils::setAppDefaults();

    echo "Spieldose installer" . PHP_EOL;

    new \Spieldose\Installer();
?>