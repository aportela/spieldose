<?php

    // Should be set to 0 in production
    error_reporting(E_ALL);

    // Should be set to '0' in production
    ini_set('display_errors', '1');

    // Timezone
    date_default_timezone_set('Europe/Berlin');

    // Settings
    $settings = [];

    // Path settings
    $settings['root'] = dirname(__DIR__);

    // Error Handling Middleware settings
    $settings['error'] = [

        // Should be set to false in production
        'display_error_details' => true,

        // Parameter is passed to the default ErrorHandler
        // View in rendered output by enabling the "displayErrorDetails" setting.
        // For the console and unit tests we also disable it
        'log_errors' => true,

        // Display error details in error log
        'log_error_details' => true,
    ];


    $settings['default_log_path'] = isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/default.log';

    // Database settings
    $settings['db'] = [
        'driver' => 'sqlite',
        'host' => '',
        'username' => '',
        'database' => 'spieldose',
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
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci'
        ],
    ];

    $settings['twig'] = [
        'path' =>  dirname(__DIR__) . DIRECTORY_SEPARATOR . 'templates',
        'options' => ['cache' => false]
    ];


    $settings['common'] = [
        'defaultResultsPage' => 64,
        'allowSignUp' => true,
        'liveSearch' => true,
        'locale' => 'en'
    ];

    $settings['albumCoverPathValidFilenames'] = '{cover,Cover,COVER}.{jpg,Jpg,JPG,jpeg,Jpeg,JPEG,png,Png,PNG}';

    $settings['phpRequiredExtensions'] = array('pdo_sqlite', 'mbstring', 'curl');

    return $settings;
