<?php
    declare(strict_types=1);

    return [
        'settings' => [
            'displayErrorDetails' => true, // set to false in production
            'addContentLengthHeader' => false, // Allow the web server to send the content-length header
            'twigParams' => [
                'siteUrl' => "http://localhost",
                'production' => false,
                'localVendorAssets' => true, // use local vendor assets (vs remote cdn)
            ],
            //
            'common' => [
                'defaultResultsPage' => 32,
            ],
            // database settings
            'database' => [
                'connectionString' => sprintf("sqlite:%s", dirname(__DIR__) . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "spieldose.sqlite3"),
                'username' => '',
                'password' => ''
            ],
            // Renderer settings
            'renderer' => [
                'template_path' => __DIR__ . '/../templates',
            ],
            // Monolog settings
            'logger' => [
                'name' => 'slim-app',
                'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
                'level' => \Monolog\Logger::DEBUG,
            ],
        ],
    ];
?>