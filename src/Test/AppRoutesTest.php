<?php

    declare(strict_types=1);

    namespace Spieldose\Test;

    require_once dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

    final class AppRoutesTest extends APITest
    {
        /**
         * Called once just like normal constructor
         */
        public static function setUpBeforeClass () { }

        /**
         * Clean up the whole test class
         */
        public static function tearDownAfterClass() { }

        /**
         * Initialize the test case
         * Called for every defined test
         */
        protected function setUp() { }

        /**
         * Clean up the test case, called for every defined test
         */
        protected function tearDown() { }

        public function testRootPath(): void {
            $this->request('GET', "/");
            $this->assertThatResponseHasStatus(200);
        }

        public function testUserPoll(): void {
            $this->request('GET', "/api/user/poll");
            $this->assertThatResponseHasStatus(403);
            $this->assertThatResponseHasContentType("application/json;charset=utf-8");
        }
    }
?>