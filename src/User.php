<?php
    declare(strict_types=1);

    namespace Spieldose;

    class User {
        public $id;
        public $email;
        public $password;
        public $passwordHash;

        /**
         * user constructor
         *
         * @param $email
         */
	    public function __construct (string $id = "", string $email = "", string $password = "") {
            $this->id = $id;
            $this->email = $email;
            $this->password = $password;
        }

        public function __destruct() { }

        /**
         * helper for hashing password (predefined algorithm)
         *
         * @param $password string the password to hash
         */
        private function passwordHash(string $password = "") {
            return(password_hash($password, PASSWORD_BCRYPT, array('cost' => 12)));
        }

        /**
         * add new user
         *
         * @param $dbh \Spieldose\Database\DB
         */
        public function add(\Spieldose\Database\DB $dbh) {
            if (! empty($this->id)) {
                if (! empty($this->email)) {
                    if (! empty($this->password)) {
                        $params = array(
                            (new \Spieldose\DataBase\DBParam())->str(":id", $this->id),
                            (new \Spieldose\DataBase\DBParam())->str(":email", mb_strtolower($this->email)),
                            (new \Spieldose\DataBase\DBParam())->str(":password_hash", $this->passwordHash($this->password))
                        );
                        return($dbh->execute("INSERT INTO USER (id, email, password_hash) VALUES(:id, :email, :password_hash)", $params));
                    } else {
                        throw new \Spieldose\Exception\NotFoundException("password");
                    }
                } else {
                    throw new \Spieldose\Exception\InvalidParamsException("email");
                }
            } else {
                throw new \Spieldose\Exception\InvalidParamsException("id");
            }
        }

        /**
         * update user
         *
         * @param $dbh \Spieldose\Database\DB
         */
        public function update(\Spieldose\Database\DB $dbh) {
            if (! empty($this->id)) {
                if (! empty($this->email)) {
                    if (! empty($this->password)) {
                        $params = array(
                            (new \Spieldose\DataBase\DBParam())->str(":id", $this->id),
                            (new \Spieldose\DataBase\DBParam())->str(":email", mb_strtolower($this->email)),
                            (new \Spieldose\DataBase\DBParam())->str(":password_hash", $this->passwordHash($this->password))
                        );
                        return($dbh->execute(" UPDATE USER SET email = :email, password_hash = :password_hash WHERE id = :id ", $params));
                    } else {
                        throw new \Spieldose\Exception\NotFoundException("password");
                    }
                } else {
                    throw new \Spieldose\Exception\InvalidParamsException("email");
                }
            } else {
                throw new \Spieldose\Exception\InvalidParamsException("id");
            }
        }

        /**
         * get user data
         *
         * @param $dbh \Spieldose\Database\DB
         */
        public function get(\Spieldose\Database\DB $dbh) {
            $results = null;
            if (! empty($this->id)) {
                $results = $dbh->query(" SELECT id, email, password_hash AS passwordHash FROM USER WHERE id = :id ", array(
                    (new \Spieldose\Database\DBParam())->str(":id", id)
                ));
            } else if (! empty($this->email)) {
                $results = $dbh->query(" SELECT id, email, password_hash AS passwordHash FROM USER WHERE email = :email ", array(
                    (new \Spieldose\Database\DBParam())->str(":email", mb_strtolower($this->email))
                ));
            } else {
                throw new \Spieldose\Exception\InvalidParamsException("id,password");
            }
            if (count($results) == 1) {
                $this->id = $results[0]->id;
                $this->email = $results[0]->email;
                $this->passwordHash = $results[0]->passwordHash;
            } else {
                throw new \Spieldose\Exception\NotFoundException("");
            }
        }

        /**
         * try login with specified credentials
         *
         * @param $dbh \Spieldose\Database\DB $dbh
         *
         * @return bool password password match
         */
        public function login(\Spieldose\Database\DB $dbh): bool {
            $this->get($dbh);
            if (password_verify($this->password, $this->passwordHash)) {
                $_SESSION["userId"] = $this->id;
                $_SESSION["email"] = $this->email;
                return(true);
            } else {
                return(false);
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
            return(isset($_SESSION["userId"]));
        }

        /**
         * return logged user id
         */
        public static function getUserId() {
            return(isset($_SESSION["userId"]) ? $_SESSION["userId"]: null);
        }

        /**
         * set user credentials
         * TODO: REMOVE
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
                    throw new \Spieldose\Exception\NotFoundException("password");
                }
            } else {
                throw new \Spieldose\Exception\InvalidParamsException("email");
            }
        }
    }

?>