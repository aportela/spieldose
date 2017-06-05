<?php

    namespace Spieldose;

    require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "include" . DIRECTORY_SEPARATOR. "configuration.php";

    \Spieldose\Utils::setAppDefaults();

    echo "Spieldose installer" . PHP_EOL;

    new \Spieldose\Installer();
?>