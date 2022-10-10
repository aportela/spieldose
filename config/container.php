<?php

use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Middleware\ErrorMiddleware;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

return [
    'settings' => function () {
        return require __DIR__ . '/settings.php';
    },

    App::class => function (ContainerInterface $container) {
        AppFactory::setContainer($container);

        return AppFactory::create();
    },

    ResponseFactoryInterface::class => function (ContainerInterface $container) {
        return $container->get(Psr17Factory::class);
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

    PDO::class => function (ContainerInterface $container) {
        $settings = $container->get('settings')['db'];

        $host = $settings['host'];
        $dbname = $settings['database'];
        $username = $settings['username'];
        $password = $settings['password'];
        $charset = $settings['charset'];
        $flags = $settings['flags'];
        $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
        $dsn = sprintf("sqlite:%s", dirname(__DIR__) . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "spieldose.sqlite3");

        return new PDO($dsn, $username, $password, $flags);
    },

    \aportela\DatabaseWrapper\DB::class => function (ContainerInterface $container) {
        $settings = $container->get('settings')['db'];
        $adapter = new \aportela\DatabaseWrapper\Adapter\PDOSQLiteAdapter(
            dirname(__DIR__) . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . $settings["database"],
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
        $logger = new \Spieldose\Logger\HTTPRequestLogger($settings['http']['name']);
        $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
        $handler = new \Monolog\Handler\RotatingFileHandler($settings['http']['path'], 0, \Monolog\Level::Debug);
        $handler->setFilenameFormat('{date}/{filename}', \Monolog\Handler\RotatingFileHandler::FILE_PER_DAY);
        $formatter = new \Monolog\Formatter\LineFormatter(null, null, true, true);
        $handler->setFormatter($formatter);
        $logger->pushHandler($handler);
        return ($logger);
    },

    \Spieldose\Logger\DefaultLogger::class => function (ContainerInterface $container) {
        $settings = $container->get('settings')['logger'];
        $logger = new \Spieldose\Logger\DefaultLogger($settings['default']['name']);
        $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
        $handler = new \Monolog\Handler\RotatingFileHandler($settings['default']['path'], 0, \Monolog\Level::Debug);
        $handler->setFilenameFormat('{date}/{filename}', \Monolog\Handler\RotatingFileHandler::FILE_PER_DAY);
        $formatter = new \Monolog\Formatter\LineFormatter(null, null, true, true);
        $handler->setFormatter($formatter);
        $logger->pushHandler($handler);
        return ($logger);
    },

    \Spieldose\Logger\DBLogger::class => function (ContainerInterface $container) {
        $settings = $container->get('settings')['logger'];
        $logger = new \Spieldose\Logger\DBLogger($settings['database']['name']);
        $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
        $handler = new \Monolog\Handler\RotatingFileHandler($settings['database']['path'], 0, \Monolog\Level::Debug);
        $handler->setFilenameFormat('{date}/{filename}', \Monolog\Handler\RotatingFileHandler::FILE_PER_DAY);
        $formatter = new \Monolog\Formatter\LineFormatter(null, null, true, true);
        $handler->setFormatter($formatter);
        $logger->pushHandler($handler);
        return ($logger);
    },

    \Spieldose\Logger\InstallerLogger::class => function (ContainerInterface $container) {
        $settings = $container->get('settings')['logger'];
        $logger = new \Spieldose\Logger\InstallerLogger($settings['installer']['name']);
        $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
        $handler = new \Monolog\Handler\RotatingFileHandler($settings['installer']['path'], 0, \Monolog\Level::Debug);
        $handler->setFilenameFormat('{date}/{filename}', \Monolog\Handler\RotatingFileHandler::FILE_PER_DAY);
        $formatter = new \Monolog\Formatter\LineFormatter(null, null, true, true);
        $handler->setFormatter($formatter);
        $logger->pushHandler($handler);
        return ($logger);
    },

    \Spieldose\Logger\ScannerLogger::class => function (ContainerInterface $container) {
        $settings = $container->get('settings')['logger'];
        $logger = new \Spieldose\Logger\ScannerLogger($settings['scanner']['name']);
        $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
        $handler = new \Monolog\Handler\RotatingFileHandler($settings['scanner']['path'], 0, \Monolog\Level::Debug);
        $handler->setFilenameFormat('{date}/{filename}', \Monolog\Handler\RotatingFileHandler::FILE_PER_DAY);
        $formatter = new \Monolog\Formatter\LineFormatter(null, null, true, true);
        $handler->setFormatter($formatter);
        $logger->pushHandler($handler);
        return ($logger);
    },

    \Spieldose\Logger\ThumbnailLogger::class => function (ContainerInterface $container) {
        $settings = $container->get('settings')['logger'];
        $logger = new \Spieldose\Logger\ThumbnailLogger($settings['thumbnail']['name']);
        $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
        $handler = new \Monolog\Handler\RotatingFileHandler($settings['thumbnail']['path'], 0, \Monolog\Level::Debug);
        $handler->setFilenameFormat('{date}/{filename}', \Monolog\Handler\RotatingFileHandler::FILE_PER_DAY);
        $formatter = new \Monolog\Formatter\LineFormatter(null, null, true, true);
        $handler->setFormatter($formatter);
        $logger->pushHandler($handler);
        return ($logger);
    },

    \Spieldose\Middleware\APIExceptionCatcher::class => function (ContainerInterface $container) {
        return (new \Spieldose\Middleware\APIExceptionCatcher($container->get(\Spieldose\Logger\HTTPRequestLogger::class)));
    }

];
