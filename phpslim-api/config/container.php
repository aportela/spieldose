<?php

use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Middleware\ErrorMiddleware;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

return [
    'settings' => function () {
        return require __DIR__ . DIRECTORY_SEPARATOR . 'settings.php';
    },

    App::class => function (ContainerInterface $container) {
        AppFactory::setContainer($container);
        return AppFactory::create();
    },

    ErrorMiddleware::class => function (ContainerInterface $container) {
        $app = $container->get(App::class);
        $settings = $container->get('settings')['error'];
        return new ErrorMiddleware(
            $app->getCallableResolver(),
            $app->getResponseFactory(),
            (bool)$settings['display_error_details'],
            (bool)$settings['log_errors'],
            (bool)$settings['log_error_details']
        );
    },

    Twig::class => function (ContainerInterface $container) {
        $settings = $container->get('settings')['twig'];
        $twig = \Slim\Views\Twig::create($settings['path'], $settings['options']);
        return $twig;
    },

    \aportela\DatabaseWrapper\DB::class => function (ContainerInterface $container) {
        $settings = $container->get('settings')['db'];
        $adapter = new \aportela\DatabaseWrapper\Adapter\PDOSQLiteAdapter(
            $settings["database"],
            // READ upgrade SQL schema file definition on next block of this README.md
            $settings["upgradeSchemaPath"]
        );
        $logger = $container->get(\Spieldose\Logger\DBLogger::class);
        // main object
        $db = new \aportela\DatabaseWrapper\DB(
            $adapter,
            $logger
        );
        return ($db);
    },

    \Spieldose\Logger\HTTPRequestLogger::class => function (ContainerInterface $container) {
        $settings = $container->get('settings')['logger'];
        $logger = new \Monolog\Logger($settings['channels']['http']['name']);
        $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
        $handler = new \Monolog\Handler\RotatingFileHandler($settings['channels']['http']['path'], 0, $settings['defaultLevel']);
        $handler->setFilenameFormat('{date}/{filename}', \Monolog\Handler\RotatingFileHandler::FILE_PER_DAY);
        $formatter = new \Monolog\Formatter\LineFormatter(null, null, true, true);
        //$handler->setFormatter($formatter);
        $logger->pushHandler($handler);
        return (new \Spieldose\Logger\HTTPRequestLogger($logger));
    },

    \Spieldose\Logger\DefaultLogger::class => function (ContainerInterface $container) {
        $settings = $container->get('settings')['logger'];
        $logger = new \Monolog\Logger($settings['channels']['default']['name']);
        $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
        $handler = new \Monolog\Handler\RotatingFileHandler($settings['channels']['default']['path'], 0, $settings['defaultLevel']);
        $handler->setFilenameFormat('{date}/{filename}', \Monolog\Handler\RotatingFileHandler::FILE_PER_DAY);
        $formatter = new \Monolog\Formatter\LineFormatter(null, null, true, true);
        //$handler->setFormatter($formatter);
        $logger->pushHandler($handler);
        return (new \Spieldose\Logger\DefaultLogger($logger));
    },

    \Spieldose\Logger\DBLogger::class => function (ContainerInterface $container) {
        $settings = $container->get('settings')['logger'];
        $logger = new \Monolog\Logger($settings['channels']['database']['name']);
        $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
        $handler = new \Monolog\Handler\RotatingFileHandler($settings['channels']['database']['path'], 0, $settings['defaultLevel']);
        $handler->setFilenameFormat('{date}/{filename}', \Monolog\Handler\RotatingFileHandler::FILE_PER_DAY);
        $formatter = new \Monolog\Formatter\LineFormatter(null, null, true, true);
        //$handler->setFormatter($formatter);
        $logger->pushHandler($handler);
        return (new \Spieldose\Logger\DBLogger($logger));
    },

    \Spieldose\Logger\InstallerLogger::class => function (ContainerInterface $container) {
        $settings = $container->get('settings')['logger'];
        $logger = new \Monolog\Logger($settings['channels']['installer']['name']);
        $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
        $handler = new \Monolog\Handler\RotatingFileHandler($settings['channels']['installer']['path'], 0, $settings['defaultLevel']);
        $handler->setFilenameFormat('{date}/{filename}', \Monolog\Handler\RotatingFileHandler::FILE_PER_DAY);
        $formatter = new \Monolog\Formatter\LineFormatter(null, null, true, true);
        //$handler->setFormatter($formatter);
        $logger->pushHandler($handler);
        return (new \Spieldose\Logger\InstallerLogger($logger));
    },

    \Spieldose\Logger\ScannerLogger::class => function (ContainerInterface $container) {
        $settings = $container->get('settings')['logger'];
        $logger = new \Monolog\Logger($settings['channels']['scanner']['name']);
        $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
        $handler = new \Monolog\Handler\RotatingFileHandler($settings['channels']['scanner']['path'], 0, $settings['defaultLevel']);
        $handler->setFilenameFormat('{date}/{filename}', \Monolog\Handler\RotatingFileHandler::FILE_PER_DAY);
        $formatter = new \Monolog\Formatter\LineFormatter(null, null, true, true);
        $handler->setFormatter($formatter);
        $logger->pushHandler($handler);
        return (new \Spieldose\Logger\ScannerLogger($logger));
    },

    \Spieldose\Logger\ScraperLogger::class => function (ContainerInterface $container) {
        $settings = $container->get('settings')['logger'];
        $logger = new \Monolog\Logger($settings['channels']['scraper']['name']);
        $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
        $handler = new \Monolog\Handler\RotatingFileHandler($settings['channels']['scraper']['path'], 0, $settings['defaultLevel']);
        $handler->setFilenameFormat('{date}/{filename}', \Monolog\Handler\RotatingFileHandler::FILE_PER_DAY);
        $formatter = new \Monolog\Formatter\LineFormatter(null, null, true, true);
        $handler->setFormatter($formatter);
        $logger->pushHandler($handler);
        return (new \Spieldose\Logger\ScraperLogger($logger));
    },

    \Spieldose\Logger\ThumbnailLogger::class => function (ContainerInterface $container) {
        $settings = $container->get('settings')['logger'];
        $logger = new \Monolog\Logger($settings['channels']['thumbnail']['name']);
        $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
        $handler = new \Monolog\Handler\RotatingFileHandler($settings['channels']['thumbnail']['path'], 0, $settings['defaultLevel']);
        $handler->setFilenameFormat('{date}/{filename}', \Monolog\Handler\RotatingFileHandler::FILE_PER_DAY);
        $formatter = new \Monolog\Formatter\LineFormatter(null, null, true, true);
        $handler->setFormatter($formatter);
        $logger->pushHandler($handler);
        return (new \Spieldose\Logger\ThumbnailLogger($logger));
    },

    \Spieldose\Middleware\APIExceptionCatcher::class => function (ContainerInterface $container) {
        return (new \Spieldose\Middleware\APIExceptionCatcher($container->get(\Spieldose\Logger\HTTPRequestLogger::class)));
    }
];
