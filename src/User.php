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
         * @param string $id
         * @param string $email
         * @param string $password
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
         * @param string $password string the password to hash
         */
        private function passwordHash(string $password = "") {
            return(password_hash($password, PASSWORD_BCRYPT, array('cost' => 12)));
        }

        /**
         * add new user
         *
         * @param \Spieldose\Database\DB $dbh database handler
         */
        public function add(\Spieldose\Database\DB $dbh) {
            if (! empty($this->id)) {
                if (! empty($this->email)) {
                    if (! empty($this->password)) {
                        $params = array(
                            (new \Spieldose\Database\DBParam())->str(":id", $this->id),
                            (new \Spieldose\Database\DBParam())->str(":email", mb_strtolower($this->email)),
                            (new \Spieldose\Database\DBParam())->str(":password_hash", $this->passwordHash($this->password))
                        );
                        return($dbh->execute("INSERT INTO USER (id, email, password_hash) VALUES(:id, :email, :password_hash)", $params));
                    } else {
                        throw new \Spieldose\Exception\InvalidParamsException("password");
                    }
                } else {
                    throw new \Spieldose\Exception\InvalidParamsException("email");
                }
            } else {
                throw new \Spieldose\Exception\InvalidParamsException("id");
            }
        }

        /**
         * update user (email & hashed password fields)
         *
         * @param \Spieldose\Database\DB $dbh database handler
         */
        public function update(\Spieldose\Database\DB $dbh) {
            if (! empty($this->id)) {
                if (! empty($this->email)) {
                    if (! empty($this->password)) {
                        $params = array(
                            (new \Spieldose\Database\DBParam())->str(":id", $this->id),
                            (new \Spieldose\Database\DBParam())->str(":email", mb_strtolower($this->email)),
                            (new \Spieldose\Database\DBParam())->str(":password_hash", $this->passwordHash($this->password))
                        );
                        return($dbh->execute(" UPDATE USER SET email = :email, password_hash = :password_hash WHERE id = :id ", $params));
                    } else {
                        throw new \Spieldose\Exception\InvalidParamsException("password");
                    }
                } else {
                    throw new \Spieldose\Exception\InvalidParamsException("email");
                }
            } else {
                throw new \Spieldose\Exception\InvalidParamsException("id");
            }
        }

        /**
         * get user data (id, email, hashed password)
         * id || email must be set
         *
         * @param \Spieldose\Database\DB $dbh database handler
         */
        public function get(\Spieldose\Database\DB $dbh) {
            $results = null;
            if (! empty($this->id)) {
                $results = $dbh->query(" SELECT id, email, password_hash AS passwordHash FROM USER WHERE id = :id ", array(
                    (new \Spieldose\Database\DBParam())->str(":id", $this->id)
                ));
            } else if (! empty($this->email)) {
                $results = $dbh->query(" SELECT id, email, password_hash AS passwordHash FROM USER WHERE email = :email ", array(
                    (new \Spieldose\Database\DBParam())->str(":email", mb_strtolower($this->email))
                ));
            } else {
                throw new \Spieldose\Exception\InvalidParamsException("id,email");
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
         * id || email & password must be set
         *
         * @param \Spieldose\Database\DB $dbh database handler
         *
         * @return bool password match (true | false)
         */
        public function login(\Spieldose\Database\DB $dbh): bool {
            if (! empty($this->password)) {
                $this->get($dbh);
                if (password_verify($this->password, $this->passwordHash)) {
                    $_SESSION["userId"] = $this->id;
                    $_SESSION["email"] = $this->email;
                    return(true);
                } else {
                    return(false);
                }
            } else {
                throw new \Spieldose\Exception\InvalidParamsException("password");
            }
        }

        /**
         * logout (close session)
         */
        public static function logout() {
            $_SESSION = array();
            if (ini_get("session.use_cookies")) {
                if (PHP_SAPI != 'cli') {
                    $params = session_get_cookie_params();
                    setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
                }
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
         *
         * @return string userId || null
         */
        public static function getUserId() {
            return(isset($_SESSION["userId"]) ? $_SESSION["userId"]: null);
        }

        /**
         * set user credentials
         * TODO: REMOVE
         *
         * @param \Spieldose\Database\DB $dbh database handler
         * @param string $password "secret :-)"
         *
         * @return bool
         */
        public function setCredentials(\Spieldose\Database\DB $dbh, string $password): bool {
            if (isset($this->id) && ! empty($this->id)) {
                if (isset($this->email) && ! empty($this->email)) {
                    if (isset($password) && ! empty($password)) {
                        $params = array(
                            (new \Spieldose\Database\DBParam())->str(":id", md5(mb_strtolower($this->email))),
                            (new \Spieldose\Database\DBParam())->str(":email", mb_strtolower($this->email)),
                            (new \Spieldose\Database\DBParam())->str(":password_hash", password_hash($password, PASSWORD_BCRYPT, array('cost' => 12)))
                        );
                        return($dbh->execute("REPLACE INTO USER (id, email, password_hash) VALUES(:id, :email, :password_hash)", $params));
                    } else {
                        throw new \Spieldose\Exception\InvalidParamsException("password");
                    }
                } else {
                    throw new \Spieldose\Exception\InvalidParamsException("email");
                }
            } else {
                throw new \Spieldose\Exception\InvalidParamsException("id");
            }
        }

    }

?>