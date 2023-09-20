<?php

declare(strict_types=1);

namespace Spieldose;

$radioStations = [
    [
        "id" => "00000000-0000-0000-0000-000000000000",
        "name" => "Nectarine",
        "url" => "https://scenestream.net/demovibes/",
        "playlist" => "http://necta.burn.net:8000/nectarine.m3u",
        "directStream" => "http://necta.burn.net:8000/nectarine",
        "images" =>
        [
            "small" => sprintf(\Spieldose\API::REMOTE_RADIOSTATION_URL_SMALL_THUMBNAIL, urlencode("https://media.radiodeck.com/stations/5f74f4b0664ee8400841cb48/profile/5fb615cd6732e6232fc7de83/xl.jpg")),
            "normal" => sprintf(\Spieldose\API::REMOTE_RADIOSTATION_URL_NORMAL_THUMBNAIL, urlencode("https://media.radiodeck.com/stations/5f74f4b0664ee8400841cb48/profile/5fb615cd6732e6232fc7de83/xl.jpg"))
        ],
        "language" => "en",
        "country" => "world",
        "tags" => ["demoscene", "videogames", "chiptunes"]
    ],
    [
        "id" => "10000000-0000-0000-0000-000000000000",
        "name" => "Cadena ser",
        "url" => "https://cadenaser.com/",
        "playlist" => null,
        "directStream" => "https://25453.live.streamtheworld.com/CADENASER.mp3",
        "images" =>
        [
            "small" => sprintf(\Spieldose\API::REMOTE_RADIOSTATION_URL_SMALL_THUMBNAIL, urlencode("https://img.huffingtonpost.es/files/image_720_480/uploads/2023/05/29/angels-barcelo-en-la-cadena-ser-2551.jpeg")),
            "normal" => sprintf(\Spieldose\API::REMOTE_RADIOSTATION_URL_NORMAL_THUMBNAIL, urlencode("https://img.huffingtonpost.es/files/image_720_480/uploads/2023/05/29/angels-barcelo-en-la-cadena-ser-2551.jpeg"))
        ],
        "language" => "es",
        "country" => "spain",
        "tags" => ["news"]
    ],
    [
        "id" => "20000000-0000-0000-0000-000000000000",
        "name" => "esRadio",
        "url" => "https://esradio.libertaddigital.com/",
        "playlist" => null,
        "directStream" => "https://libertaddigital-radio-live1.flumotion.com/libertaddigital/ld-live1-low.mp3",
        "images" =>
        [
            "small" => sprintf(\Spieldose\API::REMOTE_RADIOSTATION_URL_SMALL_THUMBNAIL, urlencode("https://i.imgur.com/E9qFOUi.png")),
            "normal" => sprintf(\Spieldose\API::REMOTE_RADIOSTATION_URL_NORMAL_THUMBNAIL, urlencode("https://i.imgur.com/E9qFOUi.png"))
        ],
        "language" => "es",
        "country" => "spain",
        "tags" => ["news"]
    ],
    [
        "id" => "40000000-0000-0000-0000-000000000000",
        "name" => "Intereconomía",
        "url" => "https://intereconomia.com/",
        "playlist" => null,
        "directStream" => "http://livestreaming3.esradio.fm/stream64.mp3",
        "images" =>
        [
            "small" => sprintf(\Spieldose\API::REMOTE_RADIOSTATION_URL_SMALL_THUMBNAIL, urlencode("https://intereconomia.com/wp-content/uploads/2016/06/toro_de_osborne.jpg")),
            "normal" => sprintf(\Spieldose\API::REMOTE_RADIOSTATION_URL_NORMAL_THUMBNAIL, urlencode("https://intereconomia.com/wp-content/uploads/2016/06/toro_de_osborne.jpg"))
        ],
        "language" => "es",
        "country" => "spain",
        "tags" => ["news"]
    ],
    [
        "id" => "50000000-0000-0000-0000-000000000000",
        "name" => "Radio Marca",
        "url" => "https://www.marca.com/radio.html",
        "playlist" => null,
        "directStream" => "https://22333.live.streamtheworld.com/RADIOMARCA_NACIONAL.mp3",
        "images" =>
        [
            "small" => sprintf(\Spieldose\API::REMOTE_RADIOSTATION_URL_SMALL_THUMBNAIL, urlencode("https://s3.amazonaws.com/arc-wordpress-client-uploads/infobae-wp/wp-content/uploads/2017/03/16145151/ronaldo-messi.jpg")),
            "normal" => sprintf(\Spieldose\API::REMOTE_RADIOSTATION_URL_NORMAL_THUMBNAIL, urlencode("https://s3.amazonaws.com/arc-wordpress-client-uploads/infobae-wp/wp-content/uploads/2017/03/16145151/ronaldo-messi.jpg"))
        ],
        "language" => "es",
        "country" => "spain",
        "tags" => ["sports"]
    ],
    [
        "id" => "60000000-0000-0000-0000-000000000000",
        "name" => "Cadena 100",
        "url" => "https://www.cadena100.es/",
        "playlist" => null,
        "directStream" => "https://server8.emitironline.com:18196/stream",
        "images" =>
        [
            "small" => sprintf(\Spieldose\API::REMOTE_RADIOSTATION_URL_SMALL_THUMBNAIL, urlencode("https://static.mytuner.mobi/media/tvos_radios/ZGJV5upXRa.png")),
            "normal" => sprintf(\Spieldose\API::REMOTE_RADIOSTATION_URL_NORMAL_THUMBNAIL, urlencode("https://static.mytuner.mobi/media/tvos_radios/ZGJV5upXRa.png"))
        ],
        "language" => "es",
        "country" => "spain",
        "tags" => ["music"]
    ],
    [
        "id" => "70000000-0000-0000-0000-000000000000",
        "name" => "Cadena Dial",
        "url" => "https://www.cadenadial.com/",
        "playlist" => null,
        "directStream" => "http://25583.live.streamtheworld.com/CADENADIAL.mp3",
        "images" =>
        [
            "small" => sprintf(\Spieldose\API::REMOTE_RADIOSTATION_URL_SMALL_THUMBNAIL, urlencode("https://static.mytuner.mobi/media/tvos_radios/L6GkAPcT26.png")),
            "normal" => sprintf(\Spieldose\API::REMOTE_RADIOSTATION_URL_NORMAL_THUMBNAIL, urlencode("https://static.mytuner.mobi/media/tvos_radios/L6GkAPcT26.png"))
        ],
        "language" => "es",
        "country" => "spain",
        "tags" => ["music"]
    ],
    [
        "id" => "80000000-0000-0000-0000-000000000000",
        "name" => "Los 40",
        "url" => "https://los40.com/",
        "playlist" => null,
        "directStream" => "https://25453.live.streamtheworld.com/LOS40.mp3",
        "images" =>
        [
            "small" => sprintf(\Spieldose\API::REMOTE_RADIOSTATION_URL_SMALL_THUMBNAIL, urlencode("https://i.imgur.com/nUgeC7Z.jpg")),
            "normal" => sprintf(\Spieldose\API::REMOTE_RADIOSTATION_URL_NORMAL_THUMBNAIL, urlencode("https://i.imgur.com/nUgeC7Z.jpg"))
        ],
        "language" => "es",
        "country" => "spain",
        "tags" => ["music"]
    ],
    [
        "id" => "90000000-0000-0000-0000-000000000000",
        "name" => "Radio Galega",
        "url" => "https://los40.com/",
        "playlist" => null,
        "directStream" => "https://wecast-b02-03.flumotion.com/radiogalega/live.mp3",
        "images" =>
        [
            "small" => sprintf(\Spieldose\API::REMOTE_RADIOSTATION_URL_SMALL_THUMBNAIL, urlencode("https://static.mytuner.mobi/media/tvos_radios/3bx3TZaBKr.png")),
            "normal" => sprintf(\Spieldose\API::REMOTE_RADIOSTATION_URL_NORMAL_THUMBNAIL, urlencode("https://static.mytuner.mobi/media/tvos_radios/3bx3TZaBKr.png"))
        ],
        "language" => "gl",
        "country" => "spain",
        "tags" => ["news"]
    ],
    [
        "id" => "11000000-0000-0000-0000-000000000000",
        "name" => "Cope",
        "url" => "https://www.cope.es/",
        "playlist" => null,
        "directStream" => "https://net1-cope-rrcast.flumotion.com/cope/net1-low.mp3",
        "images" =>
        [
            "small" => sprintf(\Spieldose\API::REMOTE_RADIOSTATION_URL_SMALL_THUMBNAIL, urlencode("https://i0.wp.com/www.enandaluz.es/wp-content/uploads/2021/09/papa-francisco.jpg")),
            "normal" => sprintf(\Spieldose\API::REMOTE_RADIOSTATION_URL_NORMAL_THUMBNAIL, urlencode("https://i0.wp.com/www.enandaluz.es/wp-content/uploads/2021/09/papa-francisco.jpg"))
        ],
        "language" => "es",
        "country" => "spain",
        "tags" => ["news"]
    ]
];

return ($radioStations);
