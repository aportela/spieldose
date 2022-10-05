<?php

use DI\ContainerBuilder;
use Slim\App;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";


$containerBuilder = new ContainerBuilder();

// Set up settings

$containerBuilder->addDefinitions(__DIR__ . '../../config/container.php');

// Build PHP-DI Container instance
$container = $containerBuilder->build();

// Create App instance
$app = $container->get(App::class);

echo "Spieldose upgrade" . PHP_EOL;

$logger = $container->get(\Monolog\Logger::class);
$logger->pushHandler(new \Monolog\Handler\StreamHandler('php://stdout', \Monolog\Level::Debug));

$logger->debug("Upgrade started");

//$app = (new \Spieldose\App())->get();

$settings = $container->get('settings');

$missingExtensions = array_diff($settings["phpRequiredExtensions"], get_loaded_extensions());
if (count($missingExtensions) > 0) {
    $logger->critical("Error: missing php extension/s: ", [implode(", ", $missingExtensions)]);
    echo "Error: missing php extension/s: " . implode(", ", $missingExtensions) . PHP_EOL;
    exit;
} else {
    $db = $container->get(DB::class);

    $success = true;
    // try to upgrade SQL schema to last version
    $currentVersion = $db->upgradeSchema();
    if ($currentVersion !== -1) {
        echo "Upgrade ok", PHP_EOL;
    } else {
        echo sprintf("Database upgrade error, check logs (at %s)%s", $settings["logger"]["path"], PHP_EOL);
    }
}
