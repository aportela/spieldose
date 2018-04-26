<?php
    declare(strict_types=1);

    return [
        'settings' => [
            'displayErrorDetails' => true, // set to false in production
            'addContentLengthHeader' => false, // Allow the web server to send the content-length header
            'twigParams' => [
                'production' => false,
                'localVendorAssets' => true // use local vendor assets (vs remote cdn)
            ],
            'phpRequiredExtensions' => array('pdo_sqlite', 'mbstring', 'curl'),
            //
            'common' => [
                'defaultResultsPage' => 128,
                'allowSignUp' => true
            ],
            // database settings
            'database' => [
                'type' => "PDO_SQLITE", // supported types: PDO_SQLITE | PDO_MARIADB
                'name' => "spieldose",
                'username' => '',
                'password' => '',
                'host' => 'localhost',
                'port' => 3306,
            ],
            // Renderer settings
            'renderer' => [
                'template_path' => __DIR__ . '/../templates',
            ],
            // Monolog settings
            'logger' => [
                'name' => 'spieldose-app',
                'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/default.log',
                'level' => \Monolog\Logger::DEBUG
            ],
            'databaseLogger' => [
                'name' => 'spieldose-db',
                'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/database.log',
                'level' => \Monolog\Logger::DEBUG
            ],
            'apiLogger' => [
                'name' => 'spieldose-api',
                'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/api.log',
                'level' => \Monolog\Logger::DEBUG
            ],
            'scanLogger' => [
                'name' => 'spieldose-scanner',
                'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/scanner.log',
                'level' => \Monolog\Logger::DEBUG
            ],
            'scrapLogger' => [
                'name' => 'spieldose-scraper',
                'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/scraper.log',
                'level' => \Monolog\Logger::DEBUG
            ],
            'thumbnailLogger' => [
                'name' => 'spieldose-thumbnail',
                'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/thumbnail.log',
                'level' => \Monolog\Logger::DEBUG
            ],
            'albumCoverPathValidFilenames' => '{cover,Cover,COVER}.{jpg,Jpg,JPG,jpeg,Jpeg,JPEG,png,Png,PNG}'
        ],
    ];
?>