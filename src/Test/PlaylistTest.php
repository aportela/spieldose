<?php

    declare(strict_types=1);

    namespace Spieldose\Test;

    require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

    final class PlaylistTest extends \PHPUnit\Framework\TestCase
    {
        static private $app = null;
        static private $container = null;
        static private $dbh = null;

        /**
         * Called once just like normal constructor
         */
        public static function setUpBeforeClass (): void {
            self::$app = (new \Spieldose\App())->get();
            self::$container = self::$app->getContainer();
            self::$dbh = new \Spieldose\Database\DB(self::$container);
        }

        /**
         * Initialize the test case
         * Called for every defined test
         */
        public function setUp(): void {
            self::$dbh->beginTransaction();
        }

        /**
         * Clean up the test case, called for every defined test
         */
        public function tearDown(): void {
            self::$dbh->rollBack();
        }

        /**
         * Clean up the whole test class
         */
        public static function tearDownAfterClass(): void {
            self::$dbh = null;
            self::$container = null;
            self::$app = null;
        }

        public function testAddWithoutId(): void {
            $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
            $this->expectExceptionMessage("id");
            (new \Spieldose\Playlist("", "", array()))->add(self::$dbh);
        }

        public function testAddWithoutName(): void {
            $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
            $this->expectExceptionMessage("name");
            (new \Spieldose\Playlist(\Ramsey\Uuid\Uuid::uuid4()->toString(), "", array()))->add(self::$dbh);
        }

        public function testAddWithoutSession(): void {
            $this->expectException(\PDOException::class);
            (new \Spieldose\Playlist(
                \Ramsey\Uuid\Uuid::uuid4()->toString(),
                "new playlist",
                array()
                )
            )->add(self::$dbh);
            $this->assertTrue(true);
        }

        public function testAddWithoutTracks(): void {
            $id = (\Ramsey\Uuid\Uuid::uuid4())->toString();
            $u = new \Spieldose\User($id, $id . "@server.com", "secret");
            $u->add(self::$dbh);
            $u->login(self::$dbh);
            (new \Spieldose\Playlist(
                \Ramsey\Uuid\Uuid::uuid4()->toString(),
                "new playlist",
                array()
                )
            )->add(self::$dbh);
            $this->assertTrue(true);
        }

        public function testAddWithTracks(): void {
            $id = (\Ramsey\Uuid\Uuid::uuid4())->toString();
            $u = new \Spieldose\User($id, $id . "@server.com", "secret");
            $u->add(self::$dbh);
            $u->login(self::$dbh);
            (
                new \Spieldose\Playlist(
                    \Ramsey\Uuid\Uuid::uuid4()->toString(),
                    "new playlist",
                    array(
                        \Ramsey\Uuid\Uuid::uuid4()->toString() // WARNING: THIS VALIDATION DO NOT WORK WITH INTEGRITY CONSTRAINTS
                    )
                )
            )->add(self::$dbh);
            $this->assertTrue(true);
        }

        public function testUpdateWithoutId(): void {
            $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
            $this->expectExceptionMessage("id");
            (new \Spieldose\Playlist("", "", array()))->update(self::$dbh);
        }

        public function testUpdateWithoutName(): void {
            $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
            $this->expectExceptionMessage("name");
            (new \Spieldose\Playlist(\Ramsey\Uuid\Uuid::uuid4()->toString(), "", array()))->update(self::$dbh);
        }

        public function testUpdate(): void {
            (new \Spieldose\Playlist(
                \Ramsey\Uuid\Uuid::uuid4()->toString(),
                "new playlist",
                array()
                )
            )->update(self::$dbh);
            $this->assertTrue(true);
        }

        public function testRemoveWithoutId(): void {
            $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
            $this->expectExceptionMessage("id");
            (new \Spieldose\Playlist("", "", array()))->remove(self::$dbh);
        }

        public function testRemove(): void {
            (new \Spieldose\Playlist(\Ramsey\Uuid\Uuid::uuid4()->toString(), "", array()))->remove(self::$dbh);
            $this->assertTrue(true);
        }


        public function testGetWithoutId(): void {
            $this->expectException(\Spieldose\Exception\InvalidParamsException::class);
            $this->expectExceptionMessage("id");
            (new \Spieldose\Playlist("", "", array()))->get(self::$dbh);
        }

        public function testGetWithoutExistentId(): void {
            $this->expectException(\Spieldose\Exception\NotFoundException::class);
            (new \Spieldose\Playlist(\Ramsey\Uuid\Uuid::uuid4()->toString(), "", array()))->get(self::$dbh);
        }

        public function testGet(): void {
            $id = (\Ramsey\Uuid\Uuid::uuid4())->toString();
            $u = new \Spieldose\User($id, $id . "@server.com", "secret");
            $u->add(self::$dbh);
            $u->login(self::$dbh);
            $playlist = new \Spieldose\Playlist(
                $id,
                "new playlist",
                array()
            );
            $playlist->add(self::$dbh);
            $playlist->get(self::$dbh);
            $this->assertTrue($playlist->id == $id);
        }

        public function testSearch(): void {
            $this->assertInstanceOf("stdclass", \Spieldose\Playlist::search(self::$dbh, 1, 16, array(), ""));
        }

        public function testSearchOrderByRandom(): void {
            $this->assertInstanceOf("stdclass", \Spieldose\Playlist::search(self::$dbh, 1, 16, array(), "random"));
        }

        public function testSearchWithPartialNameFilter(): void {
            $this->assertInstanceOf("stdclass", \Spieldose\Playlist::search(self::$dbh, 1, 16, array("partialName" => "condition"), ""));
        }

        public function testSearchWithNameFilter(): void {
            $this->assertInstanceOf("stdclass", \Spieldose\Playlist::search(self::$dbh, 1, 16, array("name" => "condition"), ""));
        }

    }
?>