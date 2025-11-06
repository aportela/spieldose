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

$settings = $container->get('settings');

$installer = new \Spieldose\Installer($logger, $container);

echo "[?] Checking php required extensions...";
if ($installer->checkRequiredPHPExtensions()) {
    echo " success!" . PHP_EOL;
} else {
    $missingPHPExtensions = $installer->getMissingPHPExtensions();
    echo " error! - missing extensions: " . implode(",", $missingPHPExtensions) . PHP_EOL;
    $logger->error("Missing php required extensions", $missingPHPExtensions);
    exit(1);
}

echo "[?] Creating missing/required paths...";
if ($installer->createMissingPaths()) {
    echo " success!" . PHP_EOL;
} else {
    echo " error!" . PHP_EOL;
    $logger->error("Error creating missing/required paths");
    exit(1);
}

$db = $container->get(\aportela\DatabaseWrapper\DB::class);

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
if ($currentDBVersion != $lastDBVersionAvailable) {
    echo "[?] Database upgrade required (current: {$currentDBVersion} => available: {$lastDBVersionAvailable})...";
    $currentVersion = $db->upgradeSchema(false);
    if ($currentVersion !== -1) {
        echo " success!" . PHP_EOL;
    } else {
        echo " error!";
        $logger->error("Error upgrading database", [$currentDBVersion, $lastDBVersionAvailable]);
        exit(1);
    }
} else {
    echo "[!] Database already on last version ({$lastDBVersionAvailable})" . PHP_EOL;
    $logger->notice("Database already on last version", [$lastDBVersionAvailable]);
}
exit(0);
