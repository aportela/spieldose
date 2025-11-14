<?php

declare(strict_types=1);

namespace Spieldose\Test;

require_once dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

final class UserSessionTest extends \Spieldose\Test\BaseTest
{
    public function testSet(): void
    {
        $id = \Spieldose\Utils::uuidv4();
        $email = "john@do.e";
        \Spieldose\UserSession::set($id, $email);
        $this->assertEquals($id, $_SESSION["userId"]);
        $this->assertEquals($email, $_SESSION["email"]);
    }

    public function testIsLogged(): void
    {
        \Spieldose\UserSession::clear();
        $this->assertFalse(\Spieldose\UserSession::isLogged());
        $id = \Spieldose\Utils::uuidv4();
        $email = "john@do.e";
        \Spieldose\UserSession::set($id, $email);
        $this->assertTrue(\Spieldose\UserSession::isLogged());
    }

    public function testGetUserId(): void
    {
        $id = \Spieldose\Utils::uuidv4();
        $email = "john@do.e";
        \Spieldose\UserSession::set($id, $email);
        $this->assertEquals($id, \Spieldose\UserSession::getUserId());
    }

    public function testGetEmail(): void
    {
        $id = \Spieldose\Utils::uuidv4();
        $email = "john@do.e";
        \Spieldose\UserSession::set($id, $email);
        $this->assertEquals($email, \Spieldose\UserSession::getEmail());
    }
}
