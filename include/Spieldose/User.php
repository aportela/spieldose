<?php

    namespace Spieldose;

    class User
    {
        private $login;
        private $passwordHash;

	    public function __construct ($login) {
            $this->login = $login;
        }

        public function __destruct() { }

        private function get(\Spieldose\Database $dbh) {
            $results = $dbh->query("SELECT password_hash AS passwordHash FROM USER WHERE login = :login", array(
                (new \Spieldose\DatabaseParam())->str(":login", $this->login)
            ));
            if (count($results) == 1) {
                $this->passwordHash = $results[0]->passwordHash;
            } else {
                throw new \Spieldose\Exception\NotFoundException("login: " . $this->login);
            }
        }

        public function login(\Spieldose\Database $dbh, string $password): bool {
            if (isset($this->login) && ! empty($this->login)) {
                if ($dbh == null) {
                    $dbh = new \Spieldose\Database();
                }
                $this->get($dbh);
                return(password_verify($password, $this->passwordHash));
            } else {
                throw new \Spieldose\Exception\InvalidParamsException("login");
            }
        }

        public function set(\Spieldose\Database $dbh, string $password) {
            if ($dbh == null) {
                $dbh = new \Spieldose\Database();
            }
            $params = array(
                (new \Spieldose\DatabaseParam())->str(":login", $this->login),
                (new \Spieldose\DatabaseParam())->str(":password_hash", password_hash($password, PASSWORD_BCRYPT, array('cost' => 12)))

            );
            $dbh->execute("REPLACE INTO USER (login, password_hash) VALUES(:login, :password_hash)", $params);
        }

    }

?>