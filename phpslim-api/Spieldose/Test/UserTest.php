<?php

declare(strict_types=1);

namespace Spieldose\Test;

require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

final class UserTest extends \Spieldose\Test\BaseTest
{
    public function testAddWithoutId(): void
    {
        if (self::$settings['common']['allowSignUp']) {
            $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
            $this->expectExceptionMessage("id");
            $id = \Spieldose\Utils::uuidv4();
            (new \Spieldose\User("", $id . "@localhost.localnet", "secret"))->add(self::$dbh);
        } else {
            $this->markTestSkipped("This test can not be run (allowSignUp disabled in settings)");
        }
    }

    public function testAddWithoutEmail(): void
    {
        if (self::$settings['common']['allowSignUp']) {
            $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
            $this->expectExceptionMessage("email");
            $id = \Spieldose\Utils::uuidv4();
            (new \Spieldose\User($id, "", "secret"))->add(self::$dbh);
        } else {
            $this->markTestSkipped("This test can not be run (allowSignUp disabled in settings)");
        }
    }

    public function testAddWithoutValidEmailLength(): void
    {
        if (self::$settings['common']['allowSignUp']) {
            $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
            $this->expectExceptionMessage("email");
            $id = \Spieldose\Utils::uuidv4();
            (new \Spieldose\User($id, str_repeat($id, 10) . "@localhost.localnet", "secret"))->add(self::$dbh);
        } else {
            $this->markTestSkipped("This test can not be run (allowSignUp disabled in settings)");
        }
    }

    public function testAddWithoutValidEmail(): void
    {
        if (self::$settings['common']['allowSignUp']) {
            $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
            $this->expectExceptionMessage("email");
            $id = \Spieldose\Utils::uuidv4();
            (new \Spieldose\User($id, $id, "secret"))->add(self::$dbh);
        } else {
            $this->markTestSkipped("This test can not be run (allowSignUp disabled in settings)");
        }
    }

    public function testAddWithoutPassword(): void
    {
        if (self::$settings['common']['allowSignUp']) {
            $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
            $this->expectExceptionMessage("password");
            $id = \Spieldose\Utils::uuidv4();
            (new \Spieldose\User($id, $id . "@localhost.localnet", ""))->add(self::$dbh);
        } else {
            $this->markTestSkipped("This test can not be run (allowSignUp disabled in settings)");
        }
    }

    public function testAdd(): void
    {
        if (self::$settings['common']['allowSignUp']) {
            $this->expectNotToPerformAssertions();
            $id = \Spieldose\Utils::uuidv4();
            (new \Spieldose\User($id, $id . "@localhost.localnet", "secret"))->add(self::$dbh);
        } else {
            $this->markTestSkipped("This test can not be run (allowSignUp disabled in settings)");
        }
    }

    public function testUpdateWithoutId(): void
    {
        $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("id");
        $id = \Spieldose\Utils::uuidv4();
        (new \Spieldose\User("", $id . "@localhost.localnet", "secret"))->update(self::$dbh);
    }

    public function testUpdateWithoutEmail(): void
    {
        $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("email");
        $id = \Spieldose\Utils::uuidv4();
        (new \Spieldose\User($id, "", "secret"))->update(self::$dbh);
    }

    public function testUpdateWithoutValidEmailLength(): void
    {
        $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("email");
        $id = \Spieldose\Utils::uuidv4();
        (new \Spieldose\User($id, str_repeat($id, 10) . "@localhost.localnet", "secret"))->update(self::$dbh);
    }

    public function testUpdateWithoutValidEmail(): void
    {
        $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("email");
        $id = \Spieldose\Utils::uuidv4();
        (new \Spieldose\User($id, $id, "secret"))->update(self::$dbh);
    }

    public function testUpdate(): void
    {
        $this->expectNotToPerformAssertions();
        $id = \Spieldose\Utils::uuidv4();
        $u = new \Spieldose\User($id, $id . "@server.com", "secret");
        $u->add(self::$dbh);
        $u->login(self::$dbh);
        $u->update(self::$dbh);
    }

    public function testGetWithoutIdOrEmail(): void
    {
        $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("id,email");
        $id = \Spieldose\Utils::uuidv4();
        (new \Spieldose\User("", "", "secret"))->get(self::$dbh);
    }

    public function testGetWithoutValidEmailLength(): void
    {
        $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("id,email");
        $id = \Spieldose\Utils::uuidv4();
        (new \Spieldose\User("", str_repeat($id, 10) . "@server.com", "secret"))->get(self::$dbh);
    }

    public function testGetWithoutValidEmail(): void
    {
        $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("id,email");
        $id = \Spieldose\Utils::uuidv4();
        (new \Spieldose\User("", $id, "secret"))->get(self::$dbh);
    }

    public function testGetWithNonExistentId(): void
    {
        $this->expectException(\Spieldose\Exception\NotFoundException::class);
        $id = \Spieldose\Utils::uuidv4();
        (new \Spieldose\User($id, $id, "secret"))->get(self::$dbh);
    }

    public function testGetWithNonExistentEmail(): void
    {
        $this->expectException(\Spieldose\Exception\NotFoundException::class);
        $id = \Spieldose\Utils::uuidv4();
        (new \Spieldose\User($id, $id . "@server.com", "secret"))->get(self::$dbh);
    }

    public function testGet(): void
    {
        $id = \Spieldose\Utils::uuidv4();
        $u = new \Spieldose\User($id, $id . "@server.com", "secret");
        $u->add(self::$dbh);
        $u->get(self::$dbh);
        $this->assertTrue($id == $u->id);
    }

    public function testExists(): void
    {
        $id = \Spieldose\Utils::uuidv4();
        $u = new \Spieldose\User($id, $id . "@server.com", "secret");
        $u->add(self::$dbh);
        $this->assertTrue($u->exists(self::$dbh));
    }

    public function testNotExists(): void
    {
        $id = \Spieldose\Utils::uuidv4();
        $u = new \Spieldose\User($id, $id . "@server.com", "secret");
        $this->assertFalse($u->exists(self::$dbh));
    }

    public function testExistsEmailWithNonExistentEmail(): void
    {
        $id = \Spieldose\Utils::uuidv4();
        $this->assertFalse(\Spieldose\User::isEmailUsed(self::$dbh, $id . "@server.com"));
    }

    public function testExistsEmailWithExistentEmail(): void
    {
        $id = \Spieldose\Utils::uuidv4();
        $u = new \Spieldose\User($id, $id . "@server.com", "secret");
        $u->add(self::$dbh);
        $this->assertTrue(\Spieldose\User::isEmailUsed(self::$dbh, $u->email));
    }

    public function testLoginWithoutIdOrEmail(): void
    {
        $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("id,email");
        $id = \Spieldose\Utils::uuidv4();
        (new \Spieldose\User("", "", "secret"))->login(self::$dbh);
    }

    public function testLoginWithoutPassword(): void
    {
        $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("password");
        $id = \Spieldose\Utils::uuidv4();
        (new \Spieldose\User($id, $id . "@server.com", ""))->login(self::$dbh);
    }

    public function testLoginWithoutExistentEmail(): void
    {
        $this->expectException(\Spieldose\Exception\NotFoundException::class);
        $id = \Spieldose\Utils::uuidv4();
        (new \Spieldose\User($id, $id . "@server.com", "secret"))->login(self::$dbh);
    }

    public function testLoginWithoutValidEmailLength(): void
    {
        $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("email");
        $id = \Spieldose\Utils::uuidv4();
        $u = new \Spieldose\User($id, $id, "secret");
        $u->add(self::$dbh);
        $u->email = str_repeat($id, 10) . "@server.com";
        $u->login(self::$dbh);
    }

    public function testLoginWithoutValidEmail(): void
    {
        $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("email");
        $id = \Spieldose\Utils::uuidv4();
        $u = new \Spieldose\User($id, $id, "secret");
        $u->add(self::$dbh);
        $u->email = $id;
        $u->login(self::$dbh);
    }

    public function testLoginWithInvalidPassword(): void
    {
        $this->expectException(\Spieldose\Exception\UnauthorizedException::class);
        $this->expectExceptionMessage("password");
        $id = \Spieldose\Utils::uuidv4();
        $u = new \Spieldose\User($id, $id . "@server.com", "secret");
        $u->add(self::$dbh);
        $u->password = "other";
        $u->login(self::$dbh);
    }

    public function testLogin(): void
    {
        $id = \Spieldose\Utils::uuidv4();
        $u = new \Spieldose\User($id, $id . "@server.com", "secret");
        $u->add(self::$dbh);
        $this->assertTrue($u->login(self::$dbh));
    }

    public function testLogout(): void
    {
        $this->assertTrue(\Spieldose\User::logout());
    }
}
