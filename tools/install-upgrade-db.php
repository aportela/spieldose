<?php

    declare(strict_types=1);

    require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";

    echo "Spieldose installer" . PHP_EOL;

    $settings = require dirname(__DIR__) . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . "AppSettings.php";

    $app = (new \Spieldose\App($settings))->get();

    $actualVersion = 1.00;
    $v = new \Spieldose\Database\Version(new \Spieldose\Database\DB());
    try {
        $actualVersion = $v->get();
    } catch (\Spieldose\Exception\NotFoundException $e) {
    } catch (\PDOException $e) {
    }
    if ($actualVersion == 1.00) {
        echo "Creating database...";
        $v->install();
        echo "ok!" , PHP_EOL;
    }
    echo sprintf("Upgrading database from version %.2f%s", $actualVersion, PHP_EOL);
    $result = $v->upgrade();
    if (count($result["successVersions"]) > 0 || count($result["failedVersions"]) > 0) {
        foreach($result["successVersions"] as $v) {
            echo sprintf(" upgrading version to: %.2f: ok!%s", $v, PHP_EOL);
        }
        foreach($result["failedVersions"] as $v) {
            echo sprintf(" upgrading version to: %.2f: error!%s", $v, PHP_EOL);
        }
    } else {
        echo "No database upgrade required" . PHP_EOL;
    }

?>