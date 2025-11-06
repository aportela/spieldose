<?php

use DI\ContainerBuilder;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$containerBuilder = new ContainerBuilder();

// Set up settings
$containerBuilder->addDefinitions(__DIR__ . '../../config/container.php');

// Build PHP-DI Container instance
$container = $containerBuilder->build();

echo "Spieldose setup" . PHP_EOL;

$logger = $container->get(\Spieldose\Logger\InstallerLogger::class);

$logger->info("Spieldose setup started");

$settings = $container->get('settings');

$installer = new \Spieldose\Installer($logger, $container);

echo "Checking php required extensions...";
if ($installer->checkRequiredPHPExtensions()) {
    echo " ok!" . PHP_EOL;
} else {
    $missingPHPExtensions = $installer->getMissingPHPExtensions();
    echo " error! - missing extensions: " . implode(",", $missingPHPExtensions) . PHP_EOL;
    $logger->error("Missing required PHP extensions", $missingPHPExtensions);
    exit(1);
}

echo "Creating required paths...";
if ($installer->createMissingPaths()) {
    echo " ok!" . PHP_EOL;
} else {
    echo " error!" . PHP_EOL;
    $logger->error("Error creating missing paths", $missingPHPExtensions);
    exit(1);
}

$db = $container->get(\aportela\DatabaseWrapper\DB::class);

// check if the database is already installed (install scheme with version table already exists)
if (!$db->isSchemaInstalled()) {
    $logger->info("Schema not found, creating database");
    if ($db->installSchema()) {
        echo "Database install success" . PHP_EOL;
    } else {
        echo "Install error, verify logs";
        $logger->critical("Database install error, verify logs");
        exit(1);
    }
} else {
    echo "Database already installed" . PHP_EOL;
    $logger->info("Database already installed");
}

$currentDBVersion = $db->getCurrentSchemaVersion();
$lastDBVersionAvailable = $db->getUpgradeSchemaVersion();

if ($currentDBVersion != $lastDBVersionAvailable) {
    echo "Database upgrade required: {$currentDBVersion} => {$lastDBVersionAvailable}" . PHP_EOL;
    $currentVersion = $db->upgradeSchema(false);
    if ($currentVersion !== -1) {
        echo "Database upgraded with success" . PHP_EOL;
        $logger->info("Database upgraded with success");
    } else {
        echo "Upgrade error, verify logs";
        $logger->critical("Upgrade error, verify logs");
        exit(1);
    }
} else {
    echo "Database upgrade not required, you are on current version: {$lastDBVersionAvailable}" . PHP_EOL;
    $logger->info("Database upgrade not required, you are on current version: {$lastDBVersionAvailable}");
}
exit(0);
