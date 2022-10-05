<?php

    use DI\ContainerBuilder;
    use Slim\App;

    require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "vendor" . DIRECTORY_SEPARATOR . "autoload.php";


    $containerBuilder = new ContainerBuilder();

    // Set up settings

    $containerBuilder->addDefinitions(__DIR__ . '../../config/container.php');

    // Build PHP-DI Container instance
    $container = $containerBuilder->build();

    // Create App instance
    $app = $container->get(App::class);

    echo "Spieldose installer" . PHP_EOL;

    $logger = $container->get(\Monolog\Logger::class);
    $logger->pushHandler(new \Monolog\Handler\StreamHandler('php://stdout', \Monolog\Level::Debug));

    $logger->debug("Installation started");

    //$app = (new \Spieldose\App())->get();

    $settings = $container->get('settings');

    $missingExtensions = array_diff($settings["phpRequiredExtensions"], get_loaded_extensions());
    if (count($missingExtensions) > 0) {
        $logger->critical("Error: missing php extension/s: ", [ implode(", ", $missingExtensions)]);
        echo "Error: missing php extension/s: " . implode(", ", $missingExtensions) . PHP_EOL;
        exit;
    } else {
        $db = $container->get(DB::class);

        $success = true;
        // check if the database is already installed (install scheme with version table already exists)
        if (! $db->isSchemaInstalled())
        {
            if ($db->installSchema())
            {
                echo "Database install success" . PHP_EOL;
            } else
            {
                echo sprintf("Database install error, check logs (at %s)%s", $settings["logger"]["path"], PHP_EOL);
                $success = false;
            }
        } else
        {
            echo "Database already installed" . PHP_EOL;
        }

        if ($success)
        {
            $results = $db->query(" SELECT release_number, release_date FROM VERSION; ");
            if (is_array($results) && count($results) == 1)
            {
                echo sprintf("Current version: %s (installed on: %s)%s", $results[0]->release_number, $results[0]->release_date, PHP_EOL);
            }
            else
            {
                echo "SQL error" . PHP_EOL;
            }
        }
    }
