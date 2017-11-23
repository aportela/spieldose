<?php

    declare(strict_types=1);

    require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

    echo "Spieldose acount manager" . PHP_EOL;

    $app = (new \Spieldose\App())->get();

    $missingExtensions = array_diff($app->getContainer()["settings"]["phpRequiredExtensions"], get_loaded_extensions());
    if (count($missingExtensions) > 0) {
        echo "Error: missing php extension/s: " . implode(", ", $missingExtensions) . PHP_EOL;
        exit;
    }

    $cmdLine = new \Spieldose\CmdLine("", array("email:", "password:"));
    if ($cmdLine->hasParam("email") && $cmdLine->hasParam("password")) {
        $c = $app->getContainer();
        $c["logger"]->info("Setting account credentials");
        echo "Setting account credentials..." . PHP_EOL;
        $dbh = new \Spieldose\Database\DB();
        if ((new \Spieldose\Database\Version($dbh))->hasUpgradeAvailable()) {
            $c["logger"]->warning("Process stopped: upgrade database before continue");
            echo "New database version available, an upgrade is required before continue." . PHP_EOL;
            exit;
        }
        $found = false;
        $u = new \Spieldose\User("", $cmdLine->getParamValue("email"), $cmdLine->getParamValue("password"));
        $c["logger"]->debug("Email: " . $u->email . " / Password: " . $u->password);
        try {
            $u->get($dbh);
            $found = true;
        } catch (\Spieldose\Exception\NotFoundException $e) { }
        if ($found) {
            $c["logger"]->debug("Account exists -> update credentials");
            echo "User found, updating password...";
            $u->update($dbh);
            echo "ok!" . PHP_EOL;
        } else {
            $c["logger"]->debug("Account not found -> adding credentials");
            echo "User not found, creating account...";
            $u->id = (\Ramsey\Uuid\Uuid::uuid4())->toString();
            $u->add($dbh);
            echo "ok!" . PHP_EOL;
        }
    } else {
        echo "No required params found: --email <email> --password <secret>" . PHP_EOL;
    }

?>