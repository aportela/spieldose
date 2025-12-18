<?php

declare(strict_types=1);

ob_start();

$app = (require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'bootstrap.php');
if (! $app instanceof Slim\App) {
    throw new \RuntimeException("Failed to create App from bootstrap");
}

$app->run();
