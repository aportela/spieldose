<?php

declare(strict_types=1);

ob_start();

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params(["SameSite" => "Strict"]);
    session_set_cookie_params(["Secure" => "true"]);
    session_set_cookie_params(["HttpOnly" => "true"]);
    session_name('SPIELDOSE');
    session_cache_limiter("nocache");
    session_start();
}

$app = (require dirname(__DIR__) . DIRECTORY_SEPARATOR .  'config' . DIRECTORY_SEPARATOR . 'bootstrap.php');
if (! $app instanceof Slim\App) {
    throw new \RuntimeException("Failed to create App from bootstrap");
}

$app->run();
