<?php

declare(strict_types=1);

ob_start();

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params(["SameSite" => "Strict"]);
    session_set_cookie_params(["Secure" => "true"]);
    session_set_cookie_params(["HttpOnly" => "true"]);
    session_id('SPIELDOSE');
    session_name('SPIELDOSE');
    session_cache_limiter("nocache");
    session_start();
}

(require dirname(__DIR__) . DIRECTORY_SEPARATOR .  'config' . DIRECTORY_SEPARATOR . 'bootstrap.php')->run();
