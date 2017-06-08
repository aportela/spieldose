<?php

    namespace Spieldose;

    class Installer
    {

        private $installQueries = array(
            "CREATE TABLE [USER] ([login] VARCHAR(32) UNIQUE NOT NULL PRIMARY KEY, [password_hash] VARCHAR(60) NOT NULL)",
            "CREATE TABLE [FILE] ([id] VARCHAR(40) UNIQUE NOT NULL PRIMARY KEY, [path] VARCHAR(2048) UNIQUE NOT NULL, title VARCHAR(128), artist VARCHAR(128), album VARCHAR(128), albumartist VARCHAR(128), discnumber INTEGER, tracknumber INTEGER, year INTEGER, genre VARCHAR(128), playtime_seconds INTEGER, playtime_string VARCHAR(16), images VARCHAR(8192))",
            "CREATE TABLE [MB_CACHE_ARTIST] ([mbid] VARCHAR(36) UNIQUE NOT NULL PRIMARY KEY, [name] VARCHAR(128) NOT NULL, [bio] TEXT)",
            "CREATE TABLE [MB_CACHE_ALBUM] ([mbid] VARCHAR(36) UNIQUE NOT NULL PRIMARY KEY, [name] VARCHAR(128) NOT NULL, [artist] VARCHAR(36) NOT NULL)",
            "PRAGMA journal_mode=WAL"
        );

	    public function __construct () {
            $cmdLine = new \Spieldose\CmdLine("", array("install", "update", "login:", "password:"));
            $paramsFound = false;
            if ($cmdLine->hasParam("login") && $cmdLine->hasParam("password")) {
                $paramsFound = true;
                $this->setUser($cmdLine->getParamValue("login"), $cmdLine->getParamValue("password"));
            }
            if ($cmdLine->hasParam("install")) {
                $paramsFound = true;
                $this->install();
            } else if ($cmdLine->hasParam("update")) {
                $paramsFound = true;
                $this->update();
            }
            if (! $paramsFound) {
                echo "Invalid params, use --install or --update", PHP_EOL;
            }
        }

        public function __destruct() { }

        private function checkRequirements(): bool {
            $success = false;
            echo "Checking requirements...";
            if (! extension_loaded('pdo_sqlite')) {
                echo PHP_EOL . "Error: pdo_sqlite php extension not found " . PHP_EOL;
            } else if (! extension_loaded('mbstring')) {
                echo PHP_EOL . "Error: mbstring php extension not found " . PHP_EOL;
            } else if (! extension_loaded('curl')) {
                echo PHP_EOL . "Error: curl php extension not found " . PHP_EOL;
            } else if (! is_writable(dirname(SQLITE_DATABASE_PATH))) {
                echo PHP_EOL . "Error: no write permissions on database directory (" . dirname(SQLITE_DATABASE_PATH) . ")" . PHP_EOL;
            } else {
                echo "ok!" . PHP_EOL;
                $success = true;
            }
            return($success);
        }

        private function install() {
            echo "Starting install..." . PHP_EOL;
            if ($this->checkRequirements()) {
                if (! file_exists(SQLITE_DATABASE_PATH)) {
                    $this->createDatabase();
                } else {
                    echo "Already installed" . PHP_EOL;
                }
            }
        }

        private function createDatabase() {
            echo "Creating database...";
            $dbh = new \Spieldose\Database();
            foreach($this->installQueries as $query) {
                $dbh->execute($query);
            }
            echo "ok!" . PHP_EOL;
        }

        private function update() {
            echo "Starting update..." . PHP_EOL;
            if ($this->checkRequirements()) {
                if (! file_exists(SQLITE_DATABASE_PATH)) {
                }
            }
            echo "ok!" . PHP_EOL;
        }

        private function setUser(string $login, string $password) {
            echo "Setting account...";
            (new \Spieldose\User($login))->set(new \Spieldose\Database(), $password);
            echo "ok!" . PHP_EOL;
        }

    }

?>