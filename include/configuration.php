<?php
    declare(strict_types = 1);

    namespace Spieldose;

    define("DEBUG", true);

    define("PDO_TYPE", "sqlite3");
    define("SQLITE_DATABASE_PATH", dirname(__DIR__) . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "spieldose.sqlite3");
    define("PDO_USERNAME", "");
    define("PDO_PASSWORD", "");
    define("PDO_CONNECTION_STRING", sprintf("sqlite:%s", SQLITE_DATABASE_PATH));

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