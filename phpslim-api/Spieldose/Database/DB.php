<?php

    declare(strict_types=1);

    namespace Spieldose\Database;

    /**
     * Simple PDO Database Wrapper
     */
    class DB {
        private $dbh = null;
        private $logger = null;
        private $container = null;
        private $queryParams = array();

	    public function __construct (\PDO $pdo, \Monolog\Logger $logger) {
            /*
            $this->container = $container;
            $settings = $this->container->get('settings');
            $options = array(
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
            );
            switch($settings['database']['type']) {
                case "PDO_SQLITE":
                    $connectionString = sprintf("sqlite:%s", dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . $settings['database']['name'] . ".sqlite3");
                break;
                case "PDO_MARIADB":
                    $connectionString = sprintf("mysql:dbname=%s;host=%s;port=%d", $settings['database']['name'], $settings['database']['host'], $settings['database']['port']);
                break;
            }
            if (! empty($connectionString)) {
                $this->dbh = new \PDO(
                    $connectionString,
                    $settings['database']['username'],
                    $settings['database']['password'],
                    $options
                );
            } else {
                throw new \Exception("Unsupported database type: " . $settings['database']['type']);
            }
            */
            $this->dbh = $pdo;
            $this->logger = $logger;
        }

        public function __destruct() {
            $this->dbh = null;
            $this->logger = null;
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

        private function replaceQueryParams($m) {
            foreach ($this->queryParams as $param) {
                if ($param->name == $m[0]) {
                    if ($param->value === null) {
                        return("NULL");
                    } else {
                        switch($param->type) {
                            case \PDO::PARAM_NULL:
                                return("NULL");
                            break;
                            case \PDO::PARAM_BOOL:
                                return($param->value ? "TRUE": "FALSE");
                            break;
                            case \PDO::PARAM_INT:
                                return($param->value);
                            break;
                            case \PDO::PARAM_STR:
                                default:
                                return("'" . str_replace("'", "''", $param->value) . "'");
                            break;
                        }
                    }
                }
            }
            return("ERROR_GETTING_QUERY_PARAM");
        }

        private function getQuery(string $query, bool $replaced = true) {
            if (! $replaced) {
                return $query;
            }
            return preg_replace_callback('/:([0-9a-z_]+)/i', array($this, 'replaceQueryParams'), $query);
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
            $this->queryParams = $params;
            $this->logger->debug($this->getQuery($sql), array('file' => __FILE__, 'line' => __LINE__));
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
            $this->queryParams = $params;
            $this->logger->debug($this->getQuery($sql), array('file' => __FILE__, 'line' => __LINE__));
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
            $this->queryParams = $params;
            $this->logger->debug($this->getQuery($sql), array('file' => __FILE__, 'line' => __LINE__));
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