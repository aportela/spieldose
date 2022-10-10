<?php

use DI\ContainerBuilder;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

$containerBuilder = new ContainerBuilder();

// Set up settings
$containerBuilder->addDefinitions(__DIR__ . '../../config/container.php');

// Build PHP-DI Container instance
$container = $containerBuilder->build();

echo "Spieldose installer" . PHP_EOL;

$logger = $container->get(InstallerLogger::class);

$logger->info("Install started");

$settings = $container->get('settings');

$missingExtensions = array_diff($settings["phpRequiredExtensions"], get_loaded_extensions());
if (count($missingExtensions) > 0) {
    $missingExtensionsStr = implode(", ", $missingExtensions);
    echo "Error: missing php extension/s: " . $missingExtensionsStr . PHP_EOL;
    $logger->critical("Error: missing php extension/s: ", [$missingExtensionsStr]);
} else {
    $db = $container->get(\aportela\DatabaseWrapper\DB::class);
    $success = true;
    // check if the database is already installed (install scheme with version table already exists)
    if (!$db->isSchemaInstalled()) {
        $logger->info("Schema not found, creating database");
        if ($db->installSchema()) {
            echo "Database install success" . PHP_EOL;
            $logger->info("Database install success");
        } else {
            echo "Install error, verify logs";
            $logger->critical("Install error, verify logs");
            $success = false;
        }
    } else {
        echo "Database already installed" . PHP_EOL;
        $logger->info("Database already installed");
    }

    if ($success) {
        $results = [];
        try {
            $results = $db->query(" SELECT release_number, release_date FROM VERSION; ");
        } catch (\Exception $e) {
            $logger->critical("SQL Error retrieving current schema version: " . $e->getMessage());
        }
        if (is_array($results) && count($results) == 1) {
            echo sprintf("Current version: %s (installed on: %s)%s", $results[0]->release_number, $results[0]->release_date, PHP_EOL);
            $logger->debug(sprintf("Current version: %s (installed on: %s)", $results[0]->release_number, $results[0]->release_date));
        } else {
            echo "SQL Error retrieving current schema version" . PHP_EOL;
        }
    }
}
