<?php

use DI\ContainerBuilder;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

$containerBuilder = new ContainerBuilder();

// Set up settings
$containerBuilder->addDefinitions(__DIR__ . '../../config/container.php');

// Build PHP-DI Container instance
$container = $containerBuilder->build();

echo "Spieldose upgrade" . PHP_EOL;

$logger = $container->get(InstallerLogger::class);

$logger->info("Upgrade started");

$settings = $container->get('settings');

$missingExtensions = array_diff($settings["phpRequiredExtensions"], get_loaded_extensions());
if (count($missingExtensions) > 0) {
    $missingExtensionsStr = implode(", ", $missingExtensions);
    echo "Error: missing php extension/s: " . $missingExtensionsStr . PHP_EOL;
    $logger->critical("Error: missing php extension/s: ", [$missingExtensionsStr]);
} else {
    $db = $container->get(DB::class);
    // try to upgrade SQL schema to last version
    $currentVersion = $db->upgradeSchema();
    if ($currentVersion !== -1) {
        echo "Database upgraded with success" . PHP_EOL;
        $logger->info("Database upgraded with success");
    } else {
        echo "Upgrade error, verify logs";
        $logger->critical("Upgrade error, verify logs");
    }
}
