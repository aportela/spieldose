<?php

    declare(strict_types = 1);

    namespace Spieldose;

    define("DEBUG", true);

    define("PDO_TYPE", "sqlite3");
    define("SQLITE_DATABASE_PATH", dirname(__DIR__) . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "spieldose.sqlite3");
    define("PDO_USERNAME", "");
    define("PDO_PASSWORD", "");
    define("PDO_CONNECTION_STRING", sprintf("sqlite:%s", SQLITE_DATABASE_PATH));

    define("DEFAULT_RESULTS_PAGE", 32);

?>