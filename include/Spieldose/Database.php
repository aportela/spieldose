<?php

    namespace Spieldose;

    class Database
    {
        private $dbh = null;

	    public function __construct () {
            $this->dbh = new \PDO(PDO_CONNECTION_STRING, PDO_USERNAME, PDO_PASSWORD, array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION));
        }

        public function __destruct() {
            $this->dbh = null;
        }

        /**
        *   Execute an SQL statement and return the number of affected rows
        */
        public function exec(string $sql, $params = array()): int   {
            $stmt = $this->dbh->prepare($sql);
            $totalParams = count($params);
            if ($totalParams > 0) {
                for ($i = 0; $i < $totalParams; $i++) {
                    $stmt->bindValue($params[$i]->name, $params[$i]->value, $params[$i]->type);
                }
            }
            return($stmt->exec());
        }

        /**
        *   Executes a prepared statement
        */
        public function execute(string $sql, $params = array()): bool  {
            $stmt = $this->dbh->prepare($sql);
            $totalParams = count($params);
            if ($totalParams > 0) {
                for ($i = 0; $i < $totalParams; $i++) {
                    $stmt->bindValue($params[$i]->name, $params[$i]->value, $params[$i]->type);
                }
            }
            return($stmt->execute());
        }

    }

?>