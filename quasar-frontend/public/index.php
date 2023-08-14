<?php

declare(strict_types=1);

ob_start();

session_cache_limiter("nocache");
session_start();

(require dirname(__DIR__) . DIRECTORY_SEPARATOR .  'config' . DIRECTORY_SEPARATOR . 'bootstrap.php')->run();
