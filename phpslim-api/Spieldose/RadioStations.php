<?php

declare(strict_types=1);

namespace Spieldose;

$radioStations = [
    [
        "id" => " 00000000-0000-0000-0000-000000000000",
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
        "id" => " 10000000-0000-0000-0000-000000000000",
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
        "id" => " 20000000-0000-0000-0000-000000000000",
        "name" => "esRadio",
        "url" => "https://esradio.libertaddigital.com/",
        "playlist" => null,
        "directStream" => "http://livestreaming3.esradio.fm/stream64.mp3",
        "images" =>
        [
            "small" => sprintf(\Spieldose\API::REMOTE_RADIOSTATION_URL_SMALL_THUMBNAIL, urlencode("https://media.vozpopuli.com/2021/02/imagen-discurso-Federico-Jimenez-Losantos_1409569095_16036296_1200x675.jpg")),
            "normal" => sprintf(\Spieldose\API::REMOTE_RADIOSTATION_URL_NORMAL_THUMBNAIL, urlencode("https://media.vozpopuli.com/2021/02/imagen-discurso-Federico-Jimenez-Losantos_1409569095_16036296_1200x675.jpg"))
        ],
        "language" => "es",
        "country" => "spain",
        "tags" => ["news"]
    ]
];

return ($radioStations);
