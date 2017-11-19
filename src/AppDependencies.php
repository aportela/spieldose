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

    $container['logger'] = function ($c) {
        $settings = $c->get('settings')['logger'];
        $logger = new \Monolog\Logger($settings['name']);
        $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
        $handler = new \Monolog\Handler\RotatingFileHandler($settings['path'], 0, $settings['level']);
        $handler->setFilenameFormat('{date}/{filename}', \Monolog\Handler\RotatingFileHandler::FILE_PER_DAY);
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

?>