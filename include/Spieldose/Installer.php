<?php

    namespace Spieldose;

    class Installer
    {

        private $installQueries = <<<EOD
CREATE TABLE [FILE] ([id] VARCHAR(40) UNIQUE NOT NULL PRIMARY KEY, [path] VARCHAR(2048) UNIQUE NOT NULL);
EOD;

	    public function __construct () {
            if ($this->checkRequirements()) {
                if (! file_exists(SQLITE_DATABASE_PATH)) {
                    $this->createDatabase();
                } else {
                    echo "Already installed" . PHP_EOL;
                }
            }
        }

        public function __destruct() { }

        private function checkRequirements(): bool {
            if (! extension_loaded('pdo_sqlite')) {
                echo "Error: pdo_sqlite php extension not found " . PHP_EOL;
            } else if (! extension_loaded('mbstring')) {
                echo "Error: mbstring php extension not found " . PHP_EOL;
            } else if (! is_writable(dirname(SQLITE_DATABASE_PATH))) {
                echo "Error: no write permissions on database directory (" . dirname(SQLITE_DATABASE_PATH) . ")" . PHP_EOL;
            } else {
                return(true);
            }
        }

        private function createDatabase() {
            echo "Creating database...";
            $dbh = new \Spieldose\Database();
            $dbh->execute($this->installQueries);
            $dbh->execute("PRAGMA journal_mode=WAL;");
            echo "ok!" . PHP_EOL;
        }
    }

?>