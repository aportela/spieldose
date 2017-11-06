<?php
    declare(strict_types=1);

    namespace Spieldose;

    class Installer {

        private $installQueries = array(
            'CREATE TABLE [USER] (
                [id] VARCHAR(32) UNIQUE NOT NULL PRIMARY KEY,
                [email] VARCHAR(255) UNIQUE NOT NULL,
                [password_hash] VARCHAR(60) NOT NULL
            )',
            'CREATE TABLE [FILE] (
                [id] VARCHAR(40) UNIQUE NOT NULL PRIMARY KEY,
                [local_path] VARCHAR(2048) UNIQUE NOT NULL,
                [track_name] VARCHAR(128),
                [track_mbid] VARCHAR(36),
                [track_artist] VARCHAR(128),
                [artist_mbid] VARCHAR(36),
                [album_name] VARCHAR(128),
                [album_mbid] VARCHAR(36),
                [album_artist] VARCHAR(128),
                [album_artist_mbid] VARCHAR(36),
                [disc_number] INTEGER,
                [track_number] VARCHAR(128),
                [year] INTEGER,
                [genre] VARCHAR(128),
                [playtime_seconds] INTEGER,
                [playtime_string] VARCHAR(16),
                [image] VARCHAR(8192),
                [created] INTEGER NOT NULL
            )',
            'CREATE TABLE [MB_CACHE_ARTIST] (
                [mbid] VARCHAR(36) UNIQUE NOT NULL PRIMARY KEY,
                [artist] VARCHAR(128) NOT NULL,
                [image] VARCHAR(8192),
                [bio] TEXT,
                [json] TEXT NOT NULL
            )',
            'CREATE TABLE [MB_CACHE_ALBUM] (
                [mbid] VARCHAR(36) UNIQUE NOT NULL PRIMARY KEY,
                [album] VARCHAR(128) NOT NULL,
                [artist] VARCHAR(36) NOT NULL,
                [image] VARCHAR(8192),
                [year] INTEGER,
                [json] TEXT NOT NULL
            )',
            'CREATE TABLE [MB_CACHE_TRACK] (
                [mbid] VARCHAR(36) UNIQUE NOT NULL PRIMARY KEY,
                [track] VARCHAR(128) NOT NULL,
                [artist_mbid] VARCHAR(36) NOT NULL,
                [artist_mbname] VARCHAR(36) NOT NULL,
                [json] TEXT NOT NULL
            )',
            'CREATE TABLE [STATS] (
                [user_id] VARCHAR(32) NOT NULL,
                [file_id] VARCHAR(40) NOT NULL,
                [played] INTEGER NOT NULL,
                PRIMARY KEY(`user_id`,`file_id`)
            )',
            'PRAGMA journal_mode=WAL'
        );

	    public function __construct () {
            $cmdLine = new \Spieldose\CmdLine("", array("install", "update", "email:", "password:"));
            $paramsFound = false;
            if ($cmdLine->hasParam("email") && $cmdLine->hasParam("password")) {
                $paramsFound = true;
                $this->setCredentials($cmdLine->getParamValue("email"), $cmdLine->getParamValue("password"));
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
            $dbh = new \Spieldose\Database\DB();
            foreach($this->installQueries as $query) {
                $dbh->execute($query);
            }
            echo "ok!" . PHP_EOL;
        }

        private function update() {
            echo "TODO" . PHP_EOL;
            /*
            echo "Starting update..." . PHP_EOL;
            if ($this->checkRequirements()) {
                if (! file_exists(SQLITE_DATABASE_PATH)) {
                }
            }
            echo "ok!" . PHP_EOL;
            */
        }

        private function setCredentials(string $email, string $password) {
            echo "Setting account credentials...";
            (new \Spieldose\User($email))->setCredentials(new \Spieldose\Database\DB(), $password);
            echo "ok!" . PHP_EOL;
        }

    }

?>