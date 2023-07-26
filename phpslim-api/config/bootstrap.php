<?php

use DI\ContainerBuilder;
use Slim\App;

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params(["SameSite" => "Strict"]);
    session_set_cookie_params(["Secure" => "true"]);
    session_set_cookie_params(["HttpOnly" => "true"]);
    session_start();
}

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$containerBuilder = new ContainerBuilder();

// Set up settings
$containerBuilder->addDefinitions(dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'container.php');

// Build PHP-DI Container instance
$container = $containerBuilder->build();

// Create App instance
$app = $container->get(App::class);


// Register routes
(require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'routes.php')($app);

// Register middleware
(require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'middleware.php')($app);

return $app;
