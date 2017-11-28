<?php

    declare(strict_types=1);

    namespace Spieldose\Test;

    require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

    final class PathTest extends \PHPUnit\Framework\TestCase
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

        public function testSearch(): void {
            $this->assertInstanceOf("stdclass", \Spieldose\Path::search(self::$dbh, 1, 16, array(), ""));
        }

        public function testSearchWithPartialNameFilter(): void {
            $this->assertInstanceOf("stdclass", \Spieldose\Path::search(self::$dbh, 1, 16, array("partialName" => "condition"), ""));
        }

        public function testSearchWithNameFilter(): void {
            $this->assertInstanceOf("stdclass", \Spieldose\Path::search(self::$dbh, 1, 16, array("name" => "condition"), ""));
        }

    }
?>