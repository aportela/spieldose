<?php
    declare(strict_types=1);

    $container = $app->getContainer();

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
        $logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
        return ($logger);
    };
?>