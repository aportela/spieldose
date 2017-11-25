<?php

    declare(strict_types=1);

    namespace Spieldose\Test;

    require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

    final class UserTest extends \PHPUnit\Framework\TestCase
    {
        static private $app = null;
        static private $container = null;
        static private $dbh = null;

        /**
         * Called once just like normal constructor
         */
        public static function setUpBeforeClass () {
            self::$app = (new \Spieldose\App())->get();
            self::$container = self::$app->getContainer();
            self::$dbh = new \Spieldose\Database\DB(self::$container);
        }

        /**
         * Initialize the test case
         * Called for every defined test
         */
        public function setUp() {
            self::$dbh->beginTransaction();
        }

        /**
         * Clean up the test case, called for every defined test
         */
        public function tearDown() {
            self::$dbh->rollBack();
        }

        /**
         * Clean up the whole test class
         */
        public static function tearDownAfterClass() {
            self::$dbh = null;
            self::$container = null;
            self::$app = null;
        }

        public function testAddWithoutId(): void {
            if (self::$container->get('settings')['common']['allowSignUp']) {
                $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
                $this->expectExceptionMessage("id");
                (new \Spieldose\User("", "", ""))->add(self::$dbh);
            } else {
                $this->markTestSkipped("This test can not be run (allowSignUp disabled in settings)");
            }
        }

        public function testAddWithoutEmail(): void {
            if (self::$container->get('settings')['common']['allowSignUp']) {
                $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
                $this->expectExceptionMessage("email");
                (new \Spieldose\User((\Ramsey\Uuid\Uuid::uuid4())->toString(), "", ""))->add(self::$dbh);
            } else {
                $this->markTestSkipped("This test can not be run (allowSignUp disabled in settings)");
            }
        }

        public function testAddWithoutValidEmail(): void {
            if (self::$container->get('settings')['common']['allowSignUp']) {
                $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
                $this->expectExceptionMessage("email");
                $id = (\Ramsey\Uuid\Uuid::uuid4())->toString();
                (new \Spieldose\User($id, $id, ""))->add(self::$dbh);
            } else {
                $this->markTestSkipped("This test can not be run (allowSignUp disabled in settings)");
            }
        }

        public function testAddWithoutPassword(): void {
            if (self::$container->get('settings')['common']['allowSignUp']) {
                $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
                $this->expectExceptionMessage("password");
                $id = (\Ramsey\Uuid\Uuid::uuid4())->toString();
                (new \Spieldose\User($id, $id . "@server.com", ""))->add(self::$dbh);
            } else {
                $this->markTestSkipped("This test can not be run (allowSignUp disabled in settings)");
            }
        }

        public function testAdd(): void {
            if (self::$container->get('settings')['common']['allowSignUp']) {
                $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
                $this->expectExceptionMessage("password");
                $id = (\Ramsey\Uuid\Uuid::uuid4())->toString();
                $this->assertTrue((new \Spieldose\User($id, $id . "@server.com", ""))->add(self::$dbh));
            } else {
                $this->markTestSkipped("This test can not be run (allowSignUp disabled in settings)");
            }
        }

        public function testUpdateWithoutId(): void {
            $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
            $this->expectExceptionMessage("id");
            (new \Spieldose\User("", "", ""))->update(self::$dbh);
        }

        public function testUpdateWithoutEmail(): void {
            $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
            $this->expectExceptionMessage("email");
            (new \Spieldose\User((\Ramsey\Uuid\Uuid::uuid4())->toString(), "", ""))->update(self::$dbh);
        }

        public function testUpdateWithoutValidEmail(): void {
            $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
            $this->expectExceptionMessage("email");
            $id = (\Ramsey\Uuid\Uuid::uuid4())->toString();
            (new \Spieldose\User($id, $id, ""))->update(self::$dbh);
        }

        public function testUpdateWithoutPassword(): void {
            $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
            $this->expectExceptionMessage("password");
            $id = (\Ramsey\Uuid\Uuid::uuid4())->toString();
            (new \Spieldose\User($id, $id . "@server.com", ""))->update(self::$dbh);
        }

        public function testUpdate(): void {
            $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
            $this->expectExceptionMessage("password");
            $id = (\Ramsey\Uuid\Uuid::uuid4())->toString();
            $u = new \Spieldose\User($id, $id . "@server.com", "");
            $this->assertTrue($u->add(self::$dbh) && $u->update(self::$dbh));
        }

        public function testGetWithoutIdOrEmail(): void {
            $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
            $this->expectExceptionMessage("id,email");
            $u = new \Spieldose\User("", "", "");
            $u->get(self::$dbh);
        }

        public function testGetWithoutValidEmail(): void {
            $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
            $this->expectExceptionMessage("id,email");
            $u = new \Spieldose\User("", (\Ramsey\Uuid\Uuid::uuid4())->toString(), "");
            $u->get(self::$dbh);
        }

        public function testGetWithNonExistentId(): void {
            $this->expectException(\Spieldose\Exception\NotFoundException::class);
            $u = new \Spieldose\User((\Ramsey\Uuid\Uuid::uuid4())->toString(), "", "");
            $u->get(self::$dbh);
        }

        public function testGetWithNonExistentEmail(): void {
            $this->expectException(\Spieldose\Exception\NotFoundException::class);
            $u = new \Spieldose\User("", (\Ramsey\Uuid\Uuid::uuid4())->toString() . "@server.com", "");
            $u->get(self::$dbh);
        }

        public function testGet(): void {
            $id = (\Ramsey\Uuid\Uuid::uuid4())->toString();
            $u = new \Spieldose\User($id, $id . "@server.com", "secret");
            $u->add(self::$dbh);
            $u->get(self::$dbh);
            $this->assertTrue($id == $u->id);
        }

        public function testLoginWithoutIdOrEmail(): void {
            $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
            $this->expectExceptionMessage("id,email");
            $this->assertTrue((new \Spieldose\User("", "", "secret"))->login(self::$dbh));
        }

        public function testLoginWithoutPassword(): void {
            $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
            $this->expectExceptionMessage("password");
            $id = (\Ramsey\Uuid\Uuid::uuid4())->toString();
            $this->assertTrue((new \Spieldose\User($id, $id . "@server.com", ""))->login(self::$dbh));
        }

        public function testLoginWithoutExistentEmail(): void {
            $this->expectException(\Spieldose\Exception\NotFoundException::class);
            $id = (\Ramsey\Uuid\Uuid::uuid4())->toString();
            $this->assertTrue((new \Spieldose\User($id, $id . "@server.com", "secret"))->login(self::$dbh));
        }

        public function testLoginWithoutValidEmail(): void {
            $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
            $id = (\Ramsey\Uuid\Uuid::uuid4())->toString();
            $this->assertTrue((new \Spieldose\User("", $id, "secret"))->login(self::$dbh));
        }

        public function testLoginWithInvalidPassword(): void {
            $id = (\Ramsey\Uuid\Uuid::uuid4())->toString();
            $u = new \Spieldose\User($id, $id . "@server.com", "secret");
            $u->add(self::$dbh);
            $u->password = "other";
            $this->assertFalse($u->login(self::$dbh));
        }

        public function testLogin(): void {
            $id = (\Ramsey\Uuid\Uuid::uuid4())->toString();
            $u = new \Spieldose\User($id, $id . "@server.com", "secret");
            $u->add(self::$dbh);
            $this->assertTrue($u->login(self::$dbh));
        }

        public function testIsLoggedWithoutSession(): void {
            \Spieldose\User::logout();
            $this->assertFalse(\Spieldose\User::isLogged());

        }

        public function testIsLogged(): void {
            $id = (\Ramsey\Uuid\Uuid::uuid4())->toString();
            $u = new \Spieldose\User($id, $id . "@server.com", "secret");
            $u->add(self::$dbh);
            $u->login(self::$dbh);
            $this->assertTrue(\Spieldose\User::isLogged());
        }

        public function testGetUserIdWithoutSession(): void {
            \Spieldose\User::logout();
            $this->assertNull(\Spieldose\User::getUserId());

        }

        public function testGetUserId(): void {
            \Spieldose\User::logout();
            $id = (\Ramsey\Uuid\Uuid::uuid4())->toString();
            $u = new \Spieldose\User($id, $id . "@server.com", "secret");
            $u->add(self::$dbh);
            $u->login(self::$dbh);
            $this->assertEquals($u->id, \Spieldose\User::getUserId());
        }

        public function testSetCredentialsWithoutId(): void {
            $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
            $this->expectExceptionMessage("id");
            $u = new \Spieldose\User("", "", "");
            $u->setCredentials(self::$dbh, "");
        }


        public function testSetCredentialsWithoutEmail(): void {
            $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
            $this->expectExceptionMessage("email");
            $u = new \Spieldose\User((\Ramsey\Uuid\Uuid::uuid4())->toString(), "", "");
            $u->setCredentials(self::$dbh, "");
        }

        public function testSetCredentialsWithoutPassword(): void {
            $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
            $this->expectExceptionMessage("password");
            $id = (\Ramsey\Uuid\Uuid::uuid4())->toString();
            $u = new \Spieldose\User($id, $id . "@server.com", "");
            $u->setCredentials(self::$dbh, "");
        }

        public function testSetCredentials(): void {
            $id = (\Ramsey\Uuid\Uuid::uuid4())->toString();
            $u = new \Spieldose\User($id, $id . "@server.com", "secret");
            $this->assertTrue($u->setCredentials(self::$dbh, "secret"));
        }

    }
?>