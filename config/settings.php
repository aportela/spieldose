<?php

// Settings
$settings = [
    'environment' => 'development' // (development|production)
];

// Should be set to 0 (E_NONE) in production
error_reporting($settings['environment'] == 'development' ? E_ALL : 0);

// Should be set to '0' in production
ini_set('display_errors', $settings['environment'] == 'development' ? '1' : '0');

// Timezone
date_default_timezone_set('Europe/Madrid');


// Path settings
$settings['root'] = dirname(__DIR__);

// Error Handling Middleware settings
$settings['error'] = [

    // Should be set to false in production
    'display_error_details' => $settings['environment'] == 'development',

    // Parameter is passed to the default ErrorHandler
    // View in rendered output by enabling the "displayErrorDetails" setting.
    // For the console and unit tests we also disable it
    'log_errors' => true,

    // Display error details in error log
    'log_error_details' => true,
];

$settings['logger'] = [
    'defaultLevel' => $settings['environment'] == 'development' ? \Monolog\Level::Debug : \Monolog\Level::Error,
    'channels' => [
        'default'  => [
            'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/default.log',
            'name' => 'Spieldose::Default'
        ],
        'http'  => [
            'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/http.log',
            'name' => 'Spieldose::HTTP'
        ],
        'installer' => [
            'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/installer.log',
            'name' => 'Spieldose::Installer'
        ],
        'scanner' => [
            'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/scanner.log',
            'name' => 'Spieldose::Scanner'
        ],
        'thumbnail' => [
            'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/thumbnail.log',
            'name' => 'Spieldose::Thumbnail'
        ],
        'database' => [
            'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/database.log',
            'name' => 'Spieldose::Database'
        ]
    ]
];

// Database settings
$settings['db'] = [
    'driver' => 'sqlite',
    'host' => '',
    'username' => '',
    'database' => 'spieldose2.sqlite3',
    'password' => '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
    'flags' => [
        // Turn off persistent connections
        PDO::ATTR_PERSISTENT => false,
        // Enable exceptions
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        // Emulate prepared statements
        PDO::ATTR_EMULATE_PREPARES => true,
        // Set default fetch mode to array
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        // Set character set
        //PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci' // BUG: https://bugs.php.net/bug.php?id=81576
    ],
    "upgradeSchemaPath" => __DIR__ . '/db-schema.php'
];

$settings['twig'] = [
    'path' =>  dirname(__DIR__) . DIRECTORY_SEPARATOR . 'templates',
    'options' => ['cache' => $settings['environment'] == 'development' ? false : dirname(__DIR__) . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "twig_cache"]
];

$settings['common'] = [
    'defaultResultsPage' => 64,
    'allowSignUp' => true,
    'liveSearch' => true,
    'locale' => 'en'
];

$settings['jwt'] = [
    'passphrase' => '/@q]/?pc`c&bq,P/MCp{5#E~-Nr2]NXQ$pvSKiz$tLQd]K)>eIOOk!&6rKVO7J~' // WARNING: for security reasons, generate a random string for using as your OWN (not default) passphrase
];

//$settings['albumCoverPathValidFilenames'] = '{cover,Cover,COVER,front,Front,FRONT}.{jpg,Jpg,JPG,jpeg,Jpeg,JPEG,png,Png,PNG}';

$settings['phpRequiredExtensions'] = array('pdo_sqlite', 'mbstring', 'curl');

$settings['webpack'] = require_once('webpack.php');

return $settings;
