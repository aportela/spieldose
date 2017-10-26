<?php
    declare(strict_types=1);

    namespace Spieldose;

    class App {
        private $app;

        public function __construct($settings) {
            $this->app = new \Slim\App($settings);
        }

        public function get() {
            return ($this->app);
        }
    }
?>