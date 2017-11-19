<?php

    declare(strict_types=1);

    namespace Spieldose;

    require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

    final class UserTest extends \PHPUnit\Framework\TestCase
    {
        /**
         * Called once just like normal constructor
         */
        public static function setUpBeforeClass () { }

        /**
         * Initialize the test case
         * Called for every defined test
         */
        public  function setUp() { }

        /**
         * Clean up the test case, called for every defined test
         */
        public function tearDown() { }

        /**
         * Clean up the whole test class
         */
        public static function tearDownAfterClass() { }

        /**
         * example test
         */
        public function testFooBar(): void {
            $this->assertTrue(true);
        }
    }
?>