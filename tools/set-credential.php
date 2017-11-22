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
        echo "Setting account credentials..." . PHP_EOL;
        $dbh = new \Spieldose\Database\DB();
        $found = false;
        $u = new \Spieldose\User("", $cmdLine->getParamValue("email"), $cmdLine->getParamValue("password"));
        try {
            $u->get($dbh);
            $found = true;
        } catch (\Spieldose\Exception\NotFoundException $e) { }
        if ($found) {
            echo "User found, updating password...";
            $u->update($dbh);
            echo "ok!" . PHP_EOL;
        } else {
            echo "User not found, creating account...";
            $u->id = (\Ramsey\Uuid\Uuid::uuid4())->toString();
            $u->add($dbh);
            echo "ok!" . PHP_EOL;
        }
    } else {
        echo "No required params found: --email <email> --password <secret>" . PHP_EOL;
    }

?>