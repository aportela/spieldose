<?php
    declare(strict_types=1);

    namespace Spieldose;

    use Slim\Http\Request;
    use Slim\Http\Response;

    class App {
        private $app;

        public function __construct() {
            $settings = require __DIR__ . '/../src/AppSettings.php';
            $this->app = new \Slim\App($settings);
            require 'AppDependencies.php';
            require 'AppRoutes.php';
        }

        public function get() {
            return ($this->app);
        }
    }
?>