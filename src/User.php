<?php

namespace Spieldose;

class User
{

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
    public function __construct(string $id = "", string $email = "", string $password = "")
    {
        $this->id = $id;
        $this->email = $email;
        $this->password = $password;
    }

    public function __destruct()
    {
    }

    /**
     * helper for hashing password (predefined algorithm)
     *
     * @param string $password string the password to hash
     */
    private function passwordHash(string $password = "")
    {
        return (password_hash($password, PASSWORD_BCRYPT, array('cost' => 12)));
    }

    /**
     * set session authentication params
     */
    public static function setSessionVars(string $userId, string $email)
    {
        $_SESSION["userId"] = $userId;
        $_SESSION["email"] = $email;
    }

    /**
     * add new user
     *
     * @param \aportela\DatabaseWrapper\DB $db database handler
     */
    public function add(\aportela\DatabaseWrapper\DB $db)
    {
        if (!empty($this->id)) {
            if (!empty($this->email) && filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
                if (!empty($this->password)) {
                    return ($db->exec(
                        " INSERT INTO USER (ID, EMAIL, PASSWORD_HASH) VALUES(:id, :email, :password_hash) ",
                        array(
                            (new \aportela\DatabaseWrapper\Param\StringParam(":id", $this->id)),
                            (new \aportela\DatabaseWrapper\Param\StringParam(":email", mb_strtolower($this->email))),
                            (new \aportela\DatabaseWrapper\Param\StringParam(":password_hash", $this->passwordHash($this->password)))
                        )
                    )
                    );
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
     * @param \aportela\DatabaseWrapper\DB $db database handler
     */
    public function update(\aportela\DatabaseWrapper\DB $db)
    {
        if (!empty($this->id)) {
            if (!empty($this->email) && filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
                if (!empty($this->password)) {
                    return ($db->exec(
                        " UPDATE USER SET EMAIL = :email, PASSWORD_HASH = :password_hash WHERE ID = :id ",
                        array(
                            (new \aportela\DatabaseWrapper\Param\StringParam(":id", $this->id)),
                            (new \aportela\DatabaseWrapper\Param\StringParam(":email", mb_strtolower($this->email))),
                            (new \aportela\DatabaseWrapper\Param\StringParam(":password_hash", $this->passwordHash($this->password)))
                        )
                    )
                    );
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
     * @param \aportela\DatabaseWrapper\DB $db database handler
     */
    public function get(\aportela\DatabaseWrapper\DB $db)
    {
        $results = null;
        if (!empty($this->id)) {
            $results = $db->query(" SELECT ID AS id, EMAIL AS email, PASSWORD_HASH AS passwordHash FROM USER WHERE ID = :id ", array(
                (new \aportela\DatabaseWrapper\Param\StringParam(":id", $this->id))
            ));
        } else if (!empty($this->email) && filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            $results = $db->query(
                " SELECT ID AS id, EMAIL AS email, PASSWORD_HASH AS passwordHash FROM USER WHERE EMAIL = :email ",
                array(
                    (new \aportela\DatabaseWrapper\Param\StringParam(":email", mb_strtolower($this->email))),
                )
            );
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
     * @param \aportela\DatabaseWrapper\DB $db database handler
     *
     * @return bool password match (true | false)
     */
    public function login(\aportela\DatabaseWrapper\DB $db): bool
    {
        if (!empty($this->password)) {
            $this->get($db);
            if (password_verify($this->password, $this->passwordHash)) {
                self::setSessionVars($this->id, $this->email);
                return (true);
            } else {
                return (false);
            }
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("password");
        }
    }

    /**
     * logout (close session)
     */
    public static function logout()
    {
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
    public static function isLogged()
    {
        return (isset($_SESSION["userId"]));
    }

    /**
     * return logged user id
     *
     * @return string userId || null
     */
    public static function getUserId()
    {
        return (isset($_SESSION["userId"]) ? $_SESSION["userId"] : null);
    }

    /**
     * set user credentials
     *
     * @param \Spieldose\Database\DB $dbh database handler
     * @param string $password "secret :-)"
     *
     * @return bool
     */
    public function setCredentials(\aportela\DatabaseWrapper\DB $db, string $password = ""): bool
    {
        if (!empty($this->id)) {
            if (!empty($this->email) && filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
                if (!empty($password)) {
                    return ($db->exec(
                        " REPLACE INTO USER (ID, EMAIL, PASSWORD_HASH) VALUES(:id, :email, :password_hash) ",
                        array(
                            (new \aportela\DatabaseWrapper\Param\StringParam(":id", $this->id)),
                            (new \aportela\DatabaseWrapper\Param\StringParam(":email", mb_strtolower($this->email))),
                            (new \aportela\DatabaseWrapper\Param\StringParam(":password_hash", password_hash($password, PASSWORD_BCRYPT, array('cost' => 12))))
                        )
                    ) == 1
                    );
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
