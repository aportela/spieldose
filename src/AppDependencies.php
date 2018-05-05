<?php
    declare(strict_types=1);

    $container = $this->app->getContainer();

    $container['view'] = function ($c) {
        $view = new \Slim\Views\Twig($c->get('settings')['renderer']['template_path'], [
            'cache' => false //'path/to/cache'
        ]);
        $basePath = rtrim(str_ireplace('index.php', '', $c['request']->getUri()->getBasePath()), '/');
        $view->addExtension(new \Slim\Views\TwigExtension($c['router'], $basePath));
        return ($view);
    };

    // enable routes in a subfolder
    // https://github.com/slimphp/Slim/issues/2294#issuecomment-341887867
    $container['environment'] = function () {
        $scriptName = $_SERVER['SCRIPT_NAME'];
        $_SERVER['SCRIPT_NAME'] = dirname(dirname($scriptName)) . '/' . basename($scriptName);
        return new Slim\Http\Environment($_SERVER);
    };

    $container['logger'] = function ($c) {
        $settings = $c->get('settings')['logger'];
        $logger = new \Monolog\Logger($settings['name']);
        $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
        $handler = new \Monolog\Handler\RotatingFileHandler($settings['path'], 0, $settings['level']);
        $handler->setFilenameFormat('{date}/{filename}', \Monolog\Handler\RotatingFileHandler::FILE_PER_DAY);
        $logger->pushHandler($handler);
        return ($logger);
    };

    $container['databaseLogger'] = function ($c) {
        $settings = $c->get('settings')['databaseLogger'];
        $logger = new \Monolog\Logger($settings['name']);
        $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
        $handler = new \Monolog\Handler\RotatingFileHandler($settings['path'], 0, $settings['level']);
        $handler->setFilenameFormat('{date}/{filename}', \Monolog\Handler\RotatingFileHandler::FILE_PER_DAY);
        $formatter = new \Monolog\Formatter\LineFormatter(null, null, true, true);
        $handler->setFormatter($formatter);
        $logger->pushHandler($handler);
        return ($logger);
    };

    $container['apiLogger'] = function ($c) {
        $settings = $c->get('settings')['apiLogger'];
        $logger = new \Monolog\Logger($settings['name']);
        $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
        $handler = new \Monolog\Handler\RotatingFileHandler($settings['path'], 0, $settings['level']);
        $handler->setFilenameFormat('{date}/{filename}', \Monolog\Handler\RotatingFileHandler::FILE_PER_DAY);
        $logger->pushHandler($handler);
        return ($logger);
    };

    $container['scanLogger'] = function ($c) {
        $settings = $c->get('settings')['scanLogger'];
        $logger = new \Monolog\Logger($settings['name']);
        $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
        $handler = new \Monolog\Handler\RotatingFileHandler($settings['path'], 0, $settings['level']);
        $handler->setFilenameFormat('{date}/{filename}', \Monolog\Handler\RotatingFileHandler::FILE_PER_DAY);
        $logger->pushHandler($handler);
        return ($logger);
    };

    $container['scrapLogger'] = function ($c) {
        $settings = $c->get('settings')['scrapLogger'];
        $logger = new \Monolog\Logger($settings['name']);
        $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
        $handler = new \Monolog\Handler\RotatingFileHandler($settings['path'], 0, $settings['level']);
        $handler->setFilenameFormat('{date}/{filename}', \Monolog\Handler\RotatingFileHandler::FILE_PER_DAY);
        $logger->pushHandler($handler);
        return ($logger);
    };

    $container['thumbnailLogger'] = function ($c) {
        $settings = $c->get('settings')['thumbnailLogger'];
        $logger = new \Monolog\Logger($settings['name']);
        $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
        $handler = new \Monolog\Handler\RotatingFileHandler($settings['path'], 0, $settings['level']);
        $handler->setFilenameFormat('{date}/{filename}', \Monolog\Handler\RotatingFileHandler::FILE_PER_DAY);
        $logger->pushHandler($handler);
        return ($logger);
    };


?>