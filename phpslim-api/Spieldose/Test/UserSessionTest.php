<?php

declare(strict_types=1);

namespace Spieldose\Test;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

final class UserSessionTest extends BaseTest
{
    public function testIsLoggedWithoutSession(): void
    {
        \Spieldose\User::signOut();
        $this->assertFalse(\Spieldose\UserSession::isLogged());
    }

    public function testIsLogged(): void
    {
        $id = \Spieldose\Utils::uuidv4();
        $u = new \Spieldose\User($id, $id . "@server.com", "secret", "johndoe");
        $u->add(self::$dbh);
        $u->signIn(self::$dbh);
        $this->assertTrue(\Spieldose\UserSession::isLogged());
    }

    public function testGetUserIdWithoutSession(): void
    {
        \Spieldose\User::signOut();
        $this->assertEmpty(\Spieldose\UserSession::getUserId());
    }

    public function testGetUserId(): void
    {
        \Spieldose\User::signOut();
        $id = \Spieldose\Utils::uuidv4();
        $u = new \Spieldose\User($id, $id . "@server.com", "secret", "johndoe");
        $u->add(self::$dbh);
        $u->signIn(self::$dbh);
        $this->assertEquals($u->id, \Spieldose\UserSession::getUserId());
    }

    public function testGetEmailWithoutSession(): void
    {
        \Spieldose\User::signOut();
        $this->assertEmpty(\Spieldose\UserSession::getEmail());
    }

    public function testGetEmail(): void
    {
        \Spieldose\User::signOut();
        $id = \Spieldose\Utils::uuidv4();
        $u = new \Spieldose\User($id, $id . "@server.com", "secret", "johndoe");
        $u->add(self::$dbh);
        $u->signIn(self::$dbh);
        $this->assertEquals($u->email, \Spieldose\UserSession::getEmail());
    }

    public function testGetNameWithoutSession(): void
    {
        \Spieldose\User::signOut();
        $this->assertEmpty(\Spieldose\UserSession::getName());
    }

    public function testGetName(): void
    {
        \Spieldose\User::signOut();
        $id = \Spieldose\Utils::uuidv4();
        $u = new \Spieldose\User($id, $id . "@server.com", "secret", "johndoe");
        $u->add(self::$dbh);
        $u->signIn(self::$dbh);
        $this->assertEquals($u->name, \Spieldose\UserSession::getName());
    }
}
