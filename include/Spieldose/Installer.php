<?php

    namespace Spieldose;

    class Installer
    {

        private $installQueries = <<<EOD
CREATE TABLE [USER] ([login] VARCHAR(32) UNIQUE NOT NULL PRIMARY KEY, [password_hash] VARCHAR(60) NOT NULL);
CREATE TABLE [FILE] ([id] VARCHAR(40) UNIQUE NOT NULL PRIMARY KEY, [path] VARCHAR(2048) UNIQUE NOT NULL);
EOD;

	    public function __construct () {
            $cmdLine = new \Spieldose\CmdLine("", array("install", "update"));
            if ($cmdLine->hasParam("install")) {
                $this->install();
            } else if ($cmdLine->hasParam("update")) {
                $this->update();
            } else {
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
            $dbh->execute($this->installQueries);
            $dbh->execute("PRAGMA journal_mode=WAL;");
            echo "ok!" . PHP_EOL;
        }

        private function update() {
            echo "Starting update..." . PHP_EOL;
            if ($this->checkRequirements()) {
                if (! file_exists(SQLITE_DATABASE_PATH)) {
                }
            }
        }

    }

?>