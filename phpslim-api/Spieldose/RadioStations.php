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
            "small" => sprintf(\Spieldose\API::REMOTE_RADIOSTATION_URL_SMALL_THUMBNAIL, urlencode("https://static.mytuner.mobi/media/tvos_radios/e6xdJAvSZu.png")),
            "normal" => sprintf(\Spieldose\API::REMOTE_RADIOSTATION_URL_NORMAL_THUMBNAIL, urlencode("https://static.mytuner.mobi/media/tvos_radios/e6xdJAvSZu.png"))
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
        "directStream" => "https://streaming.intereconomia.com/siliconorg",
        "images" =>
        [
            "small" => sprintf(\Spieldose\API::REMOTE_RADIOSTATION_URL_SMALL_THUMBNAIL, urlencode("https://media.vozpopuli.com/2021/02/imagen-discurso-Federico-Jimenez-Losantos_1409569095_16036296_1200x675.jpg")),
            "normal" => sprintf(\Spieldose\API::REMOTE_RADIOSTATION_URL_NORMAL_THUMBNAIL, urlencode("https://media.vozpopuli.com/2021/02/imagen-discurso-Federico-Jimenez-Losantos_1409569095_16036296_1200x675.jpg"))
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
            "small" => sprintf(\Spieldose\API::REMOTE_RADIOSTATION_URL_SMALL_THUMBNAIL, urlencode("https://static.mytuner.mobi/media/tvos_radios/WAsBY8S326.jpg")),
            "normal" => sprintf(\Spieldose\API::REMOTE_RADIOSTATION_URL_NORMAL_THUMBNAIL, urlencode("https://static.mytuner.mobi/media/tvos_radios/WAsBY8S326.jpg"))
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
            "small" => sprintf(\Spieldose\API::REMOTE_RADIOSTATION_URL_SMALL_THUMBNAIL, urlencode("https://upload.wikimedia.org/wikipedia/commons/thumb/9/90/RadioMARCA.svg/1200px-RadioMARCA.svg.png")),
            "normal" => sprintf(\Spieldose\API::REMOTE_RADIOSTATION_URL_NORMAL_THUMBNAIL, urlencode("https://upload.wikimedia.org/wikipedia/commons/thumb/9/90/RadioMARCA.svg/1200px-RadioMARCA.svg.png"))
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
            "small" => sprintf(\Spieldose\API::REMOTE_RADIOSTATION_URL_SMALL_THUMBNAIL, urlencode("https://static.mytuner.mobi/media/tvos_radios/8z2stvnfxzfa.jpg")),
            "normal" => sprintf(\Spieldose\API::REMOTE_RADIOSTATION_URL_NORMAL_THUMBNAIL, urlencode("https://static.mytuner.mobi/media/tvos_radios/8z2stvnfxzfa.jpg"))
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
    ]
];

return ($radioStations);
