<?php

use DI\ContainerBuilder;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

$containerBuilder = new ContainerBuilder();

// Set up container
$containerBuilder->addDefinitions(__DIR__ . '../../config/container.php');

// Build PHP-DI Container instance
$container = $containerBuilder->build();

echo "[-] Spieldose account manager" . PHP_EOL;

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

echo "[?] Checking params...";
$cmdLine = new \Spieldose\CmdLine("", ["email:", "password:"]);
if ($cmdLine->hasParam("email") && $cmdLine->hasParam("password")) {
    $email = $cmdLine->getParamValue("email");
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo ' error! - invalid email param: ' . $email . PHP_EOL;
        $logger->error("Invalid email param", [$email]);
        exit(1);
    } else {
        echo " success!" . PHP_EOL;
    }

    echo "[?] Setting account credentials...";
    $dbh = $container->get(\aportela\DatabaseWrapper\DB::class);
    if (! $dbh instanceof \aportela\DatabaseWrapper\DB) {
        echo ' error! - can not get database handler from container' . PHP_EOL;
        $logger->error("Error getting database handler from container");
        exit(1);
    }

    $found = false;
    $u = new \Spieldose\User("", $cmdLine->getParamValue("email"), $cmdLine->getParamValue("password"));
    try {
        $u->get($dbh);
        $found = true;
    } catch (\Spieldose\Exception\NotFoundException) {
    }

    if ($found) {
        echo " user found, updating password...";
        $u->update($dbh);
        echo " success!" . PHP_EOL;
    } else {
        echo " user not found, creating account...";
        $u->id = \Spieldose\Utils::uuidv4();
        $u->add($dbh);
        echo " success!" . PHP_EOL;
    }

    exit(0);
} else {
    echo " error! - No required params found: --email <email> --password <secret>" . PHP_EOL;
    exit(1);
}
