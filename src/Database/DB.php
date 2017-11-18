<?php

    declare(strict_types=1);

    namespace Spieldose\Database;

    /**
     * Simple PDO Database Wrapper
     */
    class DB {
        private $dbh = null;

	    public function __construct () {
            global $app;
            $container = $app->getContainer();
            $settings = $container->get('settings');
            $this->dbh = new \PDO($settings['database']['connectionString'], $settings['database']['username'], $settings['database']['password'], array(\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION));
        }

        public function __destruct() {
            $this->dbh = null;
        }

        /**
         * Initiates a transaction
         */
        public function beginTransaction() {
            $this->dbh->beginTransaction();
        }

        /**
         * Commits a transaction
         */
        public function commit() {
            $this->dbh->commit();
        }

        /**
         * Rolls back a transaction
         */
        public function rollBack() {
            $this->dbh->rollBack();
        }

        /**
         * Execute an SQL statement and return the number of affected rows
         *
         * @param $sql string query to execute
         * @param $params array query parameters (DBParam object array)
         *
         * @return int number of affected rows
         */
        public function exec(string $sql, $params = array()): int {
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
         * Executes a prepared statement
         *
         * @param $sql string query to execute
         * @param $params array query parameters (DBParam object array)
         *
         * @return bool
         */
        public function execute(string $sql, $params = array()): bool {
            $stmt = $this->dbh->prepare($sql);
            $totalParams = count($params);
            if ($totalParams > 0) {
                for ($i = 0; $i < $totalParams; $i++) {
                    $stmt->bindValue($params[$i]->name, $params[$i]->value, $params[$i]->type);
                }
            }
            return($stmt->execute());
        }

        /**
         * Executes an SQL statement, returning a result set as a PDOStatement object array
         *
         * @param $sql string query to execute
         * @param $params array query parameters (DBParam object array)
         *
         * @return array result set as a PDOStatement object array
         */
        public function query(string $sql, $params = array()): array {
			$rows = array();
            $stmt = $this->dbh->prepare($sql);
            $totalParams = count($params);
            if ($totalParams > 0) {
                for ($i = 0; $i < $totalParams; $i++) {
                    $stmt->bindValue($params[$i]->name, $params[$i]->value, $params[$i]->type);
                }
            }
            if ($stmt->execute()) {
                while ($row = $stmt->fetchObject()) {
                    $rows[] = $row;
                }
            }
            $stmt->closeCursor();
			return($rows);
        }
    }

?>