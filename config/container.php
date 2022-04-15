<?php

    use Nyholm\Psr7\Factory\Psr17Factory;
    use Psr\Container\ContainerInterface;
    use Psr\Http\Message\ResponseFactoryInterface;
    use Slim\App;
    use Slim\Factory\AppFactory;
    use Slim\Middleware\ErrorMiddleware;

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

        Twig::class => function (ContainerInterface $container) {
            $settings = $container->get('settings')['twig'];
            $twig = \Slim\Views\Twig::create($settings['path'], $settings['options']);

            return $twig;
        },

        \Monolog\Logger::class => function(ContainerInterface $container) {
            $settings = $container->get('settings');
            $logger = new \Monolog\Logger('default');
            $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
            $handler = new \Monolog\Handler\RotatingFileHandler($settings['default_log_path'], 0, \Monolog\Logger::DEBUG);
            $handler->setFilenameFormat('{date}/{filename}', \Monolog\Handler\RotatingFileHandler::FILE_PER_DAY);
            $formatter = new \Monolog\Formatter\LineFormatter(null, null, true, true);
            $handler->setFormatter($formatter);
            $logger->pushHandler($handler);
            return ($logger);
        }

    ];
