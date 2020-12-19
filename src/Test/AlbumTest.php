<?php

    declare(strict_types=1);

    namespace Spieldose\Test;

    require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

    final class AlbumTest extends \PHPUnit\Framework\TestCase
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

        public function testSearch(): void {
            $this->assertInstanceOf("stdclass", \Spieldose\Album::search(self::$dbh, 1, 16, array(), ""));
        }

        public function testSearchWithRandomOrder(): void {
            $this->assertInstanceOf("stdclass", \Spieldose\Album::search(self::$dbh, 1, 16, array(), "random"));
        }

        public function testSearchWithPartialNameFilter(): void {
            $this->assertInstanceOf("stdclass", \Spieldose\Album::search(self::$dbh, 1, 16, array("partialName" => "condition"), ""));
        }

        public function testSearchWithNameFilter(): void {
            $this->assertInstanceOf("stdclass", \Spieldose\Album::search(self::$dbh, 1, 16, array("name" => "condition"), ""));
        }

        public function testSearchWithPartialArtistFilter(): void {
            $this->assertInstanceOf("stdclass", \Spieldose\Album::search(self::$dbh, 1, 16, array("partialArtist" => "condition"), ""));
        }

        public function testSearchWithArtistFilter(): void {
            $this->assertInstanceOf("stdclass", \Spieldose\Album::search(self::$dbh, 1, 16, array("artist" => "condition"), ""));
        }

        public function testSearchWithYearFilter(): void {
            $this->assertInstanceOf("stdclass", \Spieldose\Album::search(self::$dbh, 1, 16, array("year" => 2000), ""));
        }

    }
?>