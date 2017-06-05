<?php

    namespace Spieldose;

    ob_start();

    require_once __DIR__ . DIRECTORY_SEPARATOR . "include" . DIRECTORY_SEPARATOR . "configuration.php";

    \Spieldose\Utils::setAppDefaults();

    echo (new \Spieldose\Page())->render();

    ob_flush();

?>