<?php

use DI\ContainerBuilder;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$containerBuilder = new ContainerBuilder();

// Set up settings
$containerBuilder->addDefinitions(__DIR__ . '../../config/container.php');

// Build PHP-DI Container instance
$container = $containerBuilder->build();

echo "[-] Spieldose setup" . PHP_EOL;

$logger = $container->get(\Spieldose\Logger\InstallerLogger::class);
if (! $logger instanceof \Spieldose\Logger\InstallerLogger) {
    echo "[E] Error getting logger from container" . PHP_EOL;
    exit(1);
}

$setup = new \Spieldose\Setup($logger);

echo "[?] Checking php required extensions...";
if ($setup->checkRequiredPHPExtensions()) {
    echo " success!" . PHP_EOL;
} else {
    $missingPHPExtensions = $setup->getMissingPHPExtensions();
    echo " error! - missing extensions: " . implode(",", $missingPHPExtensions) . PHP_EOL;
    $logger->error("Missing php required extensions", $missingPHPExtensions);
    exit(1);
}

$db = $container->get(\aportela\DatabaseWrapper\DB::class);
if (! $db instanceof \aportela\DatabaseWrapper\DB) {
    echo "[E] Error getting database handler from container" . PHP_EOL;
    $logger->error("Error getting database handler from container");
    exit(1);
}

if (!$db->isSchemaInstalled()) {
    echo "[?] Creating database base schema...";
    if ($db->installSchema()) {
        echo " success!" . PHP_EOL;
    } else {
        echo " error!";
        $logger->error("Error creating database base schema");
        exit(1);
    }
} else {
    echo "[!] Database already installed" . PHP_EOL;
    $logger->notice("Database already installed");
}

$currentDBVersion = $db->getCurrentSchemaVersion();
$lastDBVersionAvailable = $db->getUpgradeSchemaVersion();
if ($currentDBVersion !== $lastDBVersionAvailable) {
    echo sprintf('[?] Database upgrade required (current: %s => available: %s)...', $currentDBVersion, $lastDBVersionAvailable);
    $currentVersion = $db->upgradeSchema(true);
    if ($currentVersion !== -1) {
        echo " success!" . PHP_EOL;
    } else {
        echo " error!";
        $logger->error("Error upgrading database", [$currentDBVersion, $lastDBVersionAvailable]);
        exit(1);
    }
} else {
    echo sprintf('[!] Database already on last version (%s)', $lastDBVersionAvailable) . PHP_EOL;
    $logger->notice("Database already on last version", [$lastDBVersionAvailable]);
}

echo "[?] Checking required paths...";
if ($setup->createMissingPaths()) {
    echo " success!" . PHP_EOL;
} else {
    echo " error!";
    $logger->error("Error checking required paths");
    exit(1);
}

exit(0);
