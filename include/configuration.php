<?php
    declare(strict_types = 1);

    namespace Spieldose;

    define("DEBUG", true);

    spl_autoload_register(function ($className) {
        $file = __DIR__ . DIRECTORY_SEPARATOR . str_replace('\\', DIRECTORY_SEPARATOR, $className) . '.php';
        if (! file_exists($file)) {
            return (false);
        } else {
            require $file;
            return (true);
        }
    });

?>