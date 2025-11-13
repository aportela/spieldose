<?php

$environment = 'development'; // (development|production)

$basePath = dirname(__DIR__);
$dataPath = $basePath . DIRECTORY_SEPARATOR . 'data';
$logPath = $dataPath . DIRECTORY_SEPARATOR . 'logs';
$cachePath = $dataPath . DIRECTORY_SEPARATOR . 'cache';

return [
    'environment' => $environment,
    'defaultTimezone' => 'Europe/Madrid',
    'common' => [
        'allowSignUp' => true
    ],
    'jwt' => [
        // WARNING: for security reasons, generate a random string for using as your OWN (not default) passphrase
        'passphrase' => '~!yK^I7AhbnuqY@J4*Lst[g+QD6a9N5URPB?%Gf`XF(]eMrvckSm$ECx,j;3H&dV'
    ],
    'paths' => [
        'logs' => $logPath,
        'cache' => [
            "MusicBrainz" => $cachePath . DIRECTORY_SEPARATOR . "musicbrainz",
            "LastFM" => $cachePath . DIRECTORY_SEPARATOR . "lastfm",
            "Wikipedia" => $cachePath . DIRECTORY_SEPARATOR . "wikipedia",
            "Lyrics" => $cachePath . DIRECTORY_SEPARATOR . "lyrics",
        ]
    ],
    'db' => [
        //'driver' => 'sqlite',
        //'host' => '',
        //'username' => '',
        'database' => $dataPath . DIRECTORY_SEPARATOR . 'spieldose2.sqlite3',
        //'password' => '',
        //'charset' => 'utf8mb4',
        //'collation' => 'utf8mb4_unicode_ci',
        // PDO driver options
        'options' => [
            // Turn off persistent connections
            PDO::ATTR_PERSISTENT => false,
            // Enable exceptions
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            // Emulate prepared statements
            PDO::ATTR_EMULATE_PREPARES => true,
            // Set default fetch mode to array
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ],
        'upgradeSchemaPath' => $basePath . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'db-schema.php'
    ],
    // Error Handling Middleware settings
    'error' => [

        // Should be set to false in production
        /** @phpstan-ignore-next-line */
        'display_error_details' => $environment === 'development',

        // Parameter is passed to the default ErrorHandler
        // View in rendered output by enabling the 'displayErrorDetails' setting.
        // For the console and unit tests we also disable it
        'log_errors' => true,

        // Display error details in error log
        'log_error_details' => true,
    ],
    'logger' => [
        /** @phpstan-ignore-next-line */
        'defaultLevel' => $environment === 'development' ? \Monolog\Level::Debug : \Monolog\Level::Error,
        'channels' => [
            'default'  => [
                'path' => isset($_ENV['docker']) ? 'php://stdout' : $logPath . DIRECTORY_SEPARATOR . 'default.log',
                'name' => 'Homedocs::Default'
            ],
            'http'  => [
                'path' => isset($_ENV['docker']) ? 'php://stdout' : $logPath . DIRECTORY_SEPARATOR . 'http.log',
                'name' => 'Homedocs::HTTP'
            ],
            'installer' => [
                'path' => isset($_ENV['docker']) ? 'php://stdout' : $logPath . DIRECTORY_SEPARATOR . 'installer.log',
                'name' => 'Homedocs::Installer'
            ],
            'database' => [
                'path' => isset($_ENV['docker']) ? 'php://stdout' : $logPath . DIRECTORY_SEPARATOR . 'database.log',
                'name' => 'Homedocs::Database'
            ],
            'scanner' => [
                'path' => isset($_ENV['docker']) ? 'php://stdout' : $logPath . DIRECTORY_SEPARATOR . 'scanner.log',
                'name' => 'Spieldose::Scanner'
            ],
            'scraper' => [
                'path' => isset($_ENV['docker']) ? 'php://stdout' : $logPath . DIRECTORY_SEPARATOR . 'scraper.log',
                'name' => 'Spieldose::Scraper'
            ],
            'thumbnail' => [
                'path' => isset($_ENV['docker']) ? 'php://stdout' : $logPath . DIRECTORY_SEPARATOR . 'thumbnail.log',
                'name' => 'Spieldose::Thumbnail'
            ],
        ]
    ],
    'scraper' => [
        'albumCoverPathValidFilenames' => '{cover,Cover,COVER,front,Front,FRONT}.{jpg,Jpg,JPG,jpeg,Jpeg,JPEG,png,Png,PNG}'
    ],
    'LastFMAPIKey' => ""
];

/*
$settings['twig'] = [
    'path' =>  $settings['paths']['templates'],
    'options' =>  ['auto_reload' => true, 'cache' => $settings['environment'] == 'development' ? false : dirname(__DIR__) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'twig_cache']
];
*/
$settings['thumbnails'] = [
    'artists' => [
        'basePath' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'thumbnails' . DIRECTORY_SEPARATOR . 'artists',
        'sizes' => [
            'small' => [
                'width' => 100,
                'height' => 100,
                'quality' => \aportela\RemoteThumbnailCacheWrapper\JPEGThumbnail::DEFAULT_IMAGE_QUALITY
            ],
            'normal' => [
                'width' => 400,
                'height' => 400,
                'quality' => \aportela\RemoteThumbnailCacheWrapper\JPEGThumbnail::DEFAULT_IMAGE_QUALITY
            ]
        ]
    ],
    'albums' => [
        'useLocalCovers' => true, // if true, always use local cover stored on file path (cover.jpg, font.jpg...) and not the remote musicbrainz from covert art archive
        'basePath' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'thumbnails' . DIRECTORY_SEPARATOR . 'albums',
        'sizes' => [
            'small' => [
                'width' => 100,
                'height' => 100,
                'quality' => \aportela\RemoteThumbnailCacheWrapper\JPEGThumbnail::DEFAULT_IMAGE_QUALITY
            ],
            'normal' => [
                'width' => 400,
                'height' => 400,
                'quality' => \aportela\RemoteThumbnailCacheWrapper\JPEGThumbnail::DEFAULT_IMAGE_QUALITY
            ]
        ]
    ],
    'radioStations' => [
        'basePath' => dirname(__DIR__) . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'thumbnails' . DIRECTORY_SEPARATOR . 'radiostations',
        'sizes' => [
            'small' => [
                'width' => 100,
                'height' => 100,
                'quality' => \aportela\RemoteThumbnailCacheWrapper\JPEGThumbnail::DEFAULT_IMAGE_QUALITY
            ],
            'normal' => [
                'width' => 400,
                'height' => 400,
                'quality' => \aportela\RemoteThumbnailCacheWrapper\JPEGThumbnail::DEFAULT_IMAGE_QUALITY
            ]
        ]
    ],
];

$settings["cache"] = [
    "MusicBrainzCachePath" => $cachePath . DIRECTORY_SEPARATOR . "musicbrainz",
    "LastFMCachePath" => $cachePath . DIRECTORY_SEPARATOR . "lastfm",
    "LyricsCachePath" => $cachePath . DIRECTORY_SEPARATOR . "lyrics",
    "WikipediaCachePath" => $cachePath . DIRECTORY_SEPARATOR . "wikipedia"
];


// TODO: similar artists will be matched with this algorithm relevance order (based on cached data)
//$settings['similarArtistsPreferredAlgorithmRelevance'] = ["lastFMSimilar", "lastFMTag", "musicbrainzGenre", "fileID3TagGenre"];




$settings['lastFMAPIKey'] = "40ede2a05c97a8a8055ee12f813a417d"; // LAST.FM API KEY: required for scraping data from last fm

return $settings;
