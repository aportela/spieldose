<?php

use DI\ContainerBuilder;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

$containerBuilder = new ContainerBuilder();

// Set up settings
$containerBuilder->addDefinitions(__DIR__ . '../../config/container.php');

// Build PHP-DI Container instance
$container = $containerBuilder->build();

echo "Spieldose account manager" . PHP_EOL;


$logger = $container->get(\Spieldose\Logger\InstallerLogger::class);

$logger->info("Scan started");

$settings = $container->get('settings');

$missingExtensions = array_diff($settings["phpRequiredExtensions"], get_loaded_extensions());
if (count($missingExtensions) > 0) {
    $missingExtensionsStr = implode(", ", $missingExtensions);
    echo "Error: missing php extension/s: " . $missingExtensionsStr . PHP_EOL;
    $logger->critical("Error: missing php extension/s: ", [$missingExtensionsStr]);
} else {
    $cmdLine = new \Spieldose\CmdLine("", array("email:", "password:"));
    if ($cmdLine->hasParam("email") && $cmdLine->hasParam("password")) {
        echo "Setting account credentials..." . PHP_EOL;
        $db = $container->get(\aportela\DatabaseWrapper\DB::class);
        /*
        if ((new \Spieldose\Database\Version($dbh, "PDO_SQLITE"))->hasUpgradeAvailable()) {
            //$c["logger"]->warning("Process stopped: upgrade database before continue");
            echo "New database version available, an upgrade is required before continue." . PHP_EOL;
            exit;
        }
        */
        $found = false;
        $u = new \Spieldose\User("", $cmdLine->getParamValue("email"), $cmdLine->getParamValue("password"));
        try {
            $u->get($db);
            $found = true;
        } catch (\Spieldose\Exception\NotFoundException $e) {
        }
        if ($found) {
            //$c["logger"]->debug("Account exists -> update credentials");
            echo "User found, updating password...";
            $u->update($db);
            echo "ok!" . PHP_EOL;
        } else {
            //$c["logger"]->debug("Account not found -> adding credentials");
            echo "User not found, creating account...";
            $u->id = (\Ramsey\Uuid\Uuid::uuid4())->toString();
            $u->add($db);
            echo "ok!" . PHP_EOL;
        }
    } else {
        echo "No required params found: --email <email> --password <secret>" . PHP_EOL;
    }
}
