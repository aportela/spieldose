<?php

use DI\ContainerBuilder;
use Slim\App;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$containerBuilder = new ContainerBuilder();

// Set up settings
$containerBuilder->addDefinitions(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'container.php');

// Build PHP-DI Container instance
$container = $containerBuilder->build();

// Create App instance
$app = $container->get(App::class);

/**
 * Register routes
 *
 * @var callable $routesSetup
 */
$routesSetup = require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'routes.php';
$routesSetup($app);

/**
 * Register middleware
 * @var callable $middlewareSetup
 */
$middlewareSetup = (require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'middleware.php');
$middlewareSetup($app);

return $app;
