<?php

namespace Spieldose\Test;

final class UserTest extends BaseTest
{
    public function testAddWithoutId(): void
    {
        if (self::$container->get('settings')['common']['allowSignUp']) {
            $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
            $this->expectExceptionMessage("id");
            (new \Spieldose\User("", "", ""))->add(self::$db);
        } else {
            $this->markTestSkipped("This test can not be run (allowSignUp disabled in settings)");
        }
    }

    public function testAddWithoutEmail(): void
    {
        if (self::$container->get('settings')['common']['allowSignUp']) {
            $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
            $this->expectExceptionMessage("email");
            (new \Spieldose\User((\Ramsey\Uuid\Uuid::uuid4())->toString(), "", ""))->add(self::$db);
        } else {
            $this->markTestSkipped("This test can not be run (allowSignUp disabled in settings)");
        }
    }

    public function testAddWithoutValidEmail(): void
    {
        if (self::$container->get('settings')['common']['allowSignUp']) {
            $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
            $this->expectExceptionMessage("email");
            $id = (\Ramsey\Uuid\Uuid::uuid4())->toString();
            (new \Spieldose\User($id, $id, ""))->add(self::$db);
        } else {
            $this->markTestSkipped("This test can not be run (allowSignUp disabled in settings)");
        }
    }

    public function testAddWithoutPassword(): void
    {
        if (self::$container->get('settings')['common']['allowSignUp']) {
            $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
            $this->expectExceptionMessage("password");
            $id = (\Ramsey\Uuid\Uuid::uuid4())->toString();
            (new \Spieldose\User($id, $id . "@server.com", ""))->add(self::$db);
        } else {
            $this->markTestSkipped("This test can not be run (allowSignUp disabled in settings)");
        }
    }

    public function testAdd(): void
    {
        if (self::$container->get('settings')['common']['allowSignUp']) {
            $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
            $this->expectExceptionMessage("password");
            $id = (\Ramsey\Uuid\Uuid::uuid4())->toString();
            $this->assertTrue((new \Spieldose\User($id, $id . "@server.com", ""))->add(self::$db));
        } else {
            $this->markTestSkipped("This test can not be run (allowSignUp disabled in settings)");
        }
    }

    public function testUpdateWithoutId(): void
    {
        $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("id");
        (new \Spieldose\User("", "", ""))->update(self::$db);
    }

    public function testUpdateWithoutEmail(): void
    {
        $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("email");
        (new \Spieldose\User((\Ramsey\Uuid\Uuid::uuid4())->toString(), "", ""))->update(self::$db);
    }

    public function testUpdateWithoutValidEmail(): void
    {
        $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("email");
        $id = (\Ramsey\Uuid\Uuid::uuid4())->toString();
        (new \Spieldose\User($id, $id, ""))->update(self::$db);
    }

    public function testUpdateWithoutPassword(): void
    {
        $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("password");
        $id = (\Ramsey\Uuid\Uuid::uuid4())->toString();
        (new \Spieldose\User($id, $id . "@server.com", ""))->update(self::$db);
    }

    public function testUpdate(): void
    {
        $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("password");
        $id = (\Ramsey\Uuid\Uuid::uuid4())->toString();
        $u = new \Spieldose\User($id, $id . "@server.com", "");
        $this->assertTrue($u->add(self::$db) && $u->update(self::$db));
    }

    public function testGetWithoutIdOrEmail(): void
    {
        $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("id,email");
        $u = new \Spieldose\User("", "", "");
        $u->get(self::$db);
    }

    public function testGetWithoutValidEmail(): void
    {
        $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("id,email");
        $u = new \Spieldose\User("", (\Ramsey\Uuid\Uuid::uuid4())->toString(), "");
        $u->get(self::$db);
    }

    public function testGetWithNonExistentId(): void
    {
        $this->expectException(\Spieldose\Exception\NotFoundException::class);
        $u = new \Spieldose\User((\Ramsey\Uuid\Uuid::uuid4())->toString(), "", "");
        $u->get(self::$db);
    }

    public function testGetWithNonExistentEmail(): void
    {
        $this->expectException(\Spieldose\Exception\NotFoundException::class);
        $u = new \Spieldose\User("", (\Ramsey\Uuid\Uuid::uuid4())->toString() . "@server.com", "");
        $u->get(self::$db);
    }

    public function testGet(): void
    {
        $id = (\Ramsey\Uuid\Uuid::uuid4())->toString();
        $u = new \Spieldose\User($id, $id . "@server.com", "secret");
        $u->add(self::$db);
        $u->get(self::$db);
        $this->assertTrue($id == $u->id);
    }

    public function testLoginWithoutIdOrEmail(): void
    {
        $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("id,email");
        $this->assertTrue((new \Spieldose\User("", "", "secret"))->login(self::$db));
    }

    public function testLoginWithoutPassword(): void
    {
        $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("password");
        $id = (\Ramsey\Uuid\Uuid::uuid4())->toString();
        $this->assertTrue((new \Spieldose\User($id, $id . "@server.com", ""))->login(self::$db));
    }

    public function testLoginWithoutExistentEmail(): void
    {
        $this->expectException(\Spieldose\Exception\NotFoundException::class);
        $id = (\Ramsey\Uuid\Uuid::uuid4())->toString();
        $this->assertTrue((new \Spieldose\User($id, $id . "@server.com", "secret"))->login(self::$db));
    }

    public function testLoginWithoutValidEmail(): void
    {
        $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
        $id = (\Ramsey\Uuid\Uuid::uuid4())->toString();
        $this->assertTrue((new \Spieldose\User("", $id, "secret"))->login(self::$db));
    }

    public function testLoginWithInvalidPassword(): void
    {
        $id = (\Ramsey\Uuid\Uuid::uuid4())->toString();
        $u = new \Spieldose\User($id, $id . "@server.com", "secret");
        $u->add(self::$db);
        $u->password = "other";
        $this->assertFalse($u->login(self::$db));
    }

    public function testLogin(): void
    {
        $id = (\Ramsey\Uuid\Uuid::uuid4())->toString();
        $u = new \Spieldose\User($id, $id . "@server.com", "secret");
        $u->add(self::$db);
        $this->assertTrue($u->login(self::$db));
    }

    public function testIsLoggedWithoutSession(): void
    {
        \Spieldose\User::logout();
        $this->assertFalse(\Spieldose\User::isLogged());
    }

    public function testIsLogged(): void
    {
        $id = (\Ramsey\Uuid\Uuid::uuid4())->toString();
        $u = new \Spieldose\User($id, $id . "@server.com", "secret");
        $u->add(self::$db);
        $u->login(self::$db);
        $this->assertTrue(\Spieldose\User::isLogged());
    }

    public function testGetUserIdWithoutSession(): void
    {
        \Spieldose\User::logout();
        $this->assertNull(\Spieldose\User::getUserId());
    }

    public function testGetUserId(): void
    {
        \Spieldose\User::logout();
        $id = (\Ramsey\Uuid\Uuid::uuid4())->toString();
        $u = new \Spieldose\User($id, $id . "@server.com", "secret");
        $u->add(self::$db);
        $u->login(self::$db);
        $this->assertEquals($u->id, \Spieldose\User::getUserId());
    }

    public function testSetCredentialsWithoutId(): void
    {
        $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("id");
        $u = new \Spieldose\User("", "", "");
        $u->setCredentials(self::$db, "");
    }


    public function testSetCredentialsWithoutEmail(): void
    {
        $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("email");
        $u = new \Spieldose\User((\Ramsey\Uuid\Uuid::uuid4())->toString(), "", "");
        $u->setCredentials(self::$db, "");
    }

    public function testSetCredentialsWithoutPassword(): void
    {
        $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
        $this->expectExceptionMessage("password");
        $id = (\Ramsey\Uuid\Uuid::uuid4())->toString();
        $u = new \Spieldose\User($id, $id . "@server.com", "");
        $u->setCredentials(self::$db, "");
    }

    public function testSetCredentials(): void
    {
        $id = (\Ramsey\Uuid\Uuid::uuid4())->toString();
        $u = new \Spieldose\User($id, $id . "@server.com", "secret");
        $this->assertTrue($u->setCredentials(self::$db, "secret"));
    }
}
