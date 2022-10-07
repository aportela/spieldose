<?php

declare(strict_types=1);

ob_start();

session_cache_limiter("nocache");
session_start();

(require __DIR__ . '/../config/bootstrap.php')->run();
