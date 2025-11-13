<?php

use Psr\Container\ContainerInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Middleware\ErrorMiddleware;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

return [
    App::class => function (ContainerInterface $container) {
        AppFactory::setContainer($container);
        return AppFactory::create();
    },

    ErrorMiddleware::class => function (ContainerInterface $container): \Slim\Middleware\ErrorMiddleware {
        $app = $container->get(App::class);
        if (! $app instanceof \Slim\App) {
            throw new \RuntimeException("Failed to get Application (App) from container");
        }

        $settings = new \Spieldose\Settings();
        return new ErrorMiddleware(
            $app->getCallableResolver(),
            $app->getResponseFactory(),
            $settings->getErrorBooleanKey('display_error_details'),
            $settings->getErrorBooleanKey('log_errors'),
            $settings->getErrorBooleanKey('log_error_details')
        );
    },

    /*
    // TODO: remove twig
    Twig::class => function (ContainerInterface $container) {
        $settings = $container->get('settings')['twig'];
        $twig = \Slim\Views\Twig::create($settings['path'], $settings['options']);
        return $twig;
    },
    */

    \aportela\DatabaseWrapper\DB::class => function (ContainerInterface $container): \aportela\DatabaseWrapper\DB {
        $dbSettings = new \Spieldose\Settings();
        $adapter = new \aportela\DatabaseWrapper\Adapter\PDOSQLiteAdapter(
            $dbSettings->getDatabasePath(),
            $dbSettings->getDatabasePDOOptions(),
            \aportela\DatabaseWrapper\Adapter\PDOSQLiteAdapter::FLAGS_PRAGMA_JOURNAL_WAL | \aportela\DatabaseWrapper\Adapter\PDOSQLiteAdapter::FLAGS_PRAGMA_FOREIGN_KEYS_ON,
            // READ upgrade SQL schema file definition on next block of this README.md
            $dbSettings->getDatabaseUpgradeSchemaPath()
        );
        $logger = $container->get(\Spieldose\Logger\DBLogger::class);
        if (! $logger instanceof \Spieldose\Logger\DBLogger) {
            throw new \RuntimeException("Failed to get logger (DBLogger) from container");
        }

        // main object
        $db = new \aportela\DatabaseWrapper\DB(
            $adapter,
            $logger
        );
        return ($db);
    },

    \Spieldose\Logger\HTTPRequestLogger::class => function (ContainerInterface $container): \Spieldose\Logger\HTTPRequestLogger {
        $settings = new \Spieldose\Settings();

        $logger = new \Monolog\Logger($settings->getLoggerChannelProperty("http", "name"));
        $logger->pushProcessor(new \Monolog\Processor\UidProcessor());

        $handler = new \Monolog\Handler\RotatingFileHandler($settings->getLoggerChannelProperty("http", "path"), 0, $settings->getLoggerDefaultLevel());
        $handler->setFilenameFormat('{date}/{filename}', \Monolog\Handler\RotatingFileHandler::FILE_PER_DAY);
        //$formatter = new \Monolog\Formatter\LineFormatter(null, null, true, true);
        //$handler->setFormatter($formatter);
        $logger->pushHandler($handler);
        return (new \Spieldose\Logger\HTTPRequestLogger($logger));
    },

    \Spieldose\Logger\DefaultLogger::class => function (ContainerInterface $container): \Spieldose\Logger\DefaultLogger {
        $settings = new \Spieldose\Settings();

        $logger = new \Monolog\Logger($settings->getLoggerChannelProperty("default", "name"));
        $logger->pushProcessor(new \Monolog\Processor\UidProcessor());

        $handler = new \Monolog\Handler\RotatingFileHandler($settings->getLoggerChannelProperty("default", "path"), 0, $settings->getLoggerDefaultLevel());
        $handler->setFilenameFormat('{date}/{filename}', \Monolog\Handler\RotatingFileHandler::FILE_PER_DAY);
        //$formatter = new \Monolog\Formatter\LineFormatter(null, null, true, true);
        //$handler->setFormatter($formatter);
        $logger->pushHandler($handler);
        return (new \Spieldose\Logger\DefaultLogger($logger));
    },

    \Spieldose\Logger\DBLogger::class => function (ContainerInterface $container): \Spieldose\Logger\DBLogger {
        $settings = new \Spieldose\Settings();

        $logger = new \Monolog\Logger($settings->getLoggerChannelProperty("database", "name"));
        $logger->pushProcessor(new \Monolog\Processor\UidProcessor());

        $handler = new \Monolog\Handler\RotatingFileHandler($settings->getLoggerChannelProperty("database", "path"), 0, $settings->getLoggerDefaultLevel());
        $handler->setFilenameFormat('{date}/{filename}', \Monolog\Handler\RotatingFileHandler::FILE_PER_DAY);
        //$formatter = new \Monolog\Formatter\LineFormatter(null, null, true, true);
        //$handler->setFormatter($formatter);
        $logger->pushHandler($handler);
        return (new \Spieldose\Logger\DBLogger($logger));
    },

    \Spieldose\Logger\InstallerLogger::class => function (ContainerInterface $container): \Spieldose\Logger\InstallerLogger {
        $settings = new \Spieldose\Settings();

        $logger = new \Monolog\Logger($settings->getLoggerChannelProperty("installer", "name"));
        $logger->pushProcessor(new \Monolog\Processor\UidProcessor());

        $handler = new \Monolog\Handler\RotatingFileHandler($settings->getLoggerChannelProperty("installer", "path"), 0, $settings->getLoggerDefaultLevel());
        $handler->setFilenameFormat('{date}/{filename}', \Monolog\Handler\RotatingFileHandler::FILE_PER_DAY);
        //$formatter = new \Monolog\Formatter\LineFormatter(null, null, true, true);
        //$handler->setFormatter($formatter);
        $logger->pushHandler($handler);
        return (new \Spieldose\Logger\InstallerLogger($logger));
    },

    \Spieldose\Logger\ScannerLogger::class => function (ContainerInterface $container) {
        $settings = new \Spieldose\Settings();

        $logger = new \Monolog\Logger($settings->getLoggerChannelProperty("scanner", "name"));
        $logger->pushProcessor(new \Monolog\Processor\UidProcessor());

        $handler = new \Monolog\Handler\RotatingFileHandler($settings->getLoggerChannelProperty("scanner", "path"), 0, $settings->getLoggerDefaultLevel());
        $handler->setFilenameFormat('{date}/{filename}', \Monolog\Handler\RotatingFileHandler::FILE_PER_DAY);
        //$formatter = new \Monolog\Formatter\LineFormatter(null, null, true, true);
        //$handler->setFormatter($formatter);
        $logger->pushHandler($handler);
        return (new \Spieldose\Logger\ScannerLogger($logger));
    },

    \Spieldose\Logger\ScraperLogger::class => function (ContainerInterface $container) {
        $settings = new \Spieldose\Settings();

        $logger = new \Monolog\Logger($settings->getLoggerChannelProperty("scraper", "name"));
        $logger->pushProcessor(new \Monolog\Processor\UidProcessor());

        $handler = new \Monolog\Handler\RotatingFileHandler($settings->getLoggerChannelProperty("scraper", "path"), 0, $settings->getLoggerDefaultLevel());
        $handler->setFilenameFormat('{date}/{filename}', \Monolog\Handler\RotatingFileHandler::FILE_PER_DAY);
        //$formatter = new \Monolog\Formatter\LineFormatter(null, null, true, true);
        //$handler->setFormatter($formatter);
        $logger->pushHandler($handler);
        return (new \Spieldose\Logger\ScraperLogger($logger));
    },

    \Spieldose\Logger\ThumbnailLogger::class => function (ContainerInterface $container) {
        $settings = new \Spieldose\Settings();

        $logger = new \Monolog\Logger($settings->getLoggerChannelProperty("thumbnail", "name"));
        $logger->pushProcessor(new \Monolog\Processor\UidProcessor());

        $handler = new \Monolog\Handler\RotatingFileHandler($settings->getLoggerChannelProperty("thumbnail", "path"), 0, $settings->getLoggerDefaultLevel());
        $handler->setFilenameFormat('{date}/{filename}', \Monolog\Handler\RotatingFileHandler::FILE_PER_DAY);
        //$formatter = new \Monolog\Formatter\LineFormatter(null, null, true, true);
        //$handler->setFormatter($formatter);
        $logger->pushHandler($handler);
        return (new \Spieldose\Logger\ThumbnailLogger($logger));
    },

    \Spieldose\Middleware\APIExceptionCatcher::class => function (ContainerInterface $container): \Spieldose\Middleware\APIExceptionCatcher {
        $logger = $container->get(\Spieldose\Logger\HTTPRequestLogger::class);
        if (! $logger instanceof \Spieldose\Logger\HTTPRequestLogger) {
            throw new \RuntimeException("Failed to get logger (HTTPRequestLogger) from container");
        }

        return (new \Spieldose\Middleware\APIExceptionCatcher($logger));
    }
];
