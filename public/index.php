<?php

    declare(strict_types=1);

    ob_start();

    require __DIR__ . '/../vendor/autoload.php';

    session_cache_limiter("nocache");
    session_start();

    $app = (new \Spieldose\App())->get();

    $app->run();

?>