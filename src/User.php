<?php
    declare(strict_types=1);

    namespace Spieldose;

    class User {
        private $id;
        private $email;
        private $passwordHash;

        /**
         * user constructor
         *
         * @param $email
         */
	    public function __construct ($email) {
            $this->email = mb_strtolower($email);
            $this->id = md5(mb_strtolower($email));
        }

        public function __destruct() { }

        /**
         * get user data (email must be set)
         *
         * @param $dbh \Spieldose\Database\DB
         */
        private function get(\Spieldose\Database\DB $dbh) {
            $results = $dbh->query("SELECT password_hash AS passwordHash FROM USER WHERE email = :email", array(
                (new \Spieldose\Database\DBParam())->str(":email", mb_strtolower($this->email))
            ));
            if (count($results) == 1) {
                $this->passwordHash = $results[0]->passwordHash;
            } else {
                throw new \Spieldose\Exception\NotFoundException("email");
            }
        }

        /**
         * try login with specified credentials
         *
         * @param $dbh \Spieldose\Database\DB $dbh
         * @param $password string "secret :-)"
         *
         * @return bool password password match
         */
        public function login(\Spieldose\Database\DB $dbh, string $password): bool {
            if (isset($this->email) && ! empty($this->email)) {
                if (isset($password) && ! empty($password)) {
                    $this->get($dbh);
                    if (password_verify($password, $this->passwordHash)) {
                        $_SESSION["userId"] = $this->id;
                        $_SESSION["email"] = $this->email;
                        return(true);
                    } else {
                        return(false);
                    }
                } else {
                    throw new \Spieldose\Exception\InvalidParamsException("password");
                }
            } else {
                throw new \Spieldose\Exception\InvalidParamsException("email");
            }
        }

        /**
         * logout (close session)
         */
        public static function logout() {
            $_SESSION = array();
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
            }
            if (session_status() != PHP_SESSION_NONE) {
                session_destroy();
            }
        }

        /**
         * check if user is logged
         */
        public static function isLogged() {
            return(isset($_SESSION["email"]));
        }

        /**
         * return logged user id
         */
        public static function getUserId() {
            return(isset($_SESSION["userId"]) ? $_SESSION["userId"]: null);
        }

        /**
         * set user credentials
         *
         * @param $dbh \Spieldose\Database\DB $dbh
         * @param $password string "secret :-)"
         *
         * @return bool
         */
        public function setCredentials(\Spieldose\Database\DB $dbh, string $password): bool {
            if (isset($this->email) && ! empty($this->email)) {
                if (isset($password) && ! empty($password)) {
                    $params = array(
                        (new \Spieldose\DataBase\DBParam())->str(":id", md5(mb_strtolower($this->email))),
                        (new \Spieldose\DataBase\DBParam())->str(":email", mb_strtolower($this->email)),
                        (new \Spieldose\DataBase\DBParam())->str(":password_hash", password_hash($password, PASSWORD_BCRYPT, array('cost' => 12)))
                    );
                    return($dbh->execute("REPLACE INTO USER (id, email, password_hash) VALUES(:id, :email, :password_hash)", $params));
                } else {
                    throw new \Spieldose\Exception\NotFoundException("email");
                }
            } else {
                throw new \Spieldose\Exception\InvalidParamsException("email");
            }
        }
    }

?>