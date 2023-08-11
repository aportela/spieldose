<?php

return (array(
    1 => array(
        '
            PRAGMA foreign_keys = ON;
        ',
        '
            CREATE TABLE `USER` (
                `id` CHAR(36) NOT NULL,
                `email` VARCHAR(255) NOT NULL UNIQUE,
                `password_hash` CHAR(60) NOT NULL,
                PRIMARY KEY (`id`)
            );
        ',
        '
            CREATE TABLE `DIRECTORY` (
                `id` CHAR(36) NOT NULL,
                `path` VARCHAR(4096) NOT NULL UNIQUE,
                `mtime` INTEGER NOT NULL,
                `cover_filename` VARCHAR(4096),
                PRIMARY KEY (`id`)
            );
        ',
        '
            CREATE TABLE `FILE` (
                `id` CHAR(36) NOT NULL,
                `directory_id` CHAR(36) NOT NULL,
                `name` VARCHAR(255) NOT NULL,
                `mtime` INTEGER NOT NULL,
                PRIMARY KEY (`id`),
                FOREIGN KEY(`directory_id`) REFERENCES DIRECTORY(`id`),
                UNIQUE(`directory_id`, `name`)
            );
        ',
        '
            CREATE TABLE `FILE_ID3_TAG` (
                `id` CHAR(36) NOT NULL,
                `title` VARCHAR(128),
                `artist` VARCHAR(128),
                `album_artist` VARCHAR(128),
                `album` VARCHAR(128),
                `year` CHAR(4),
                `genre` VARCHAR(128),
                `track_number` INT,
                `disc_number` INT,
                `playtime_seconds` INT,
                `mime` VARCHAR(127),
                `mb_artist_id` CHAR(36),
                `mb_album_artist_id` CHAR(36),
                `mb_album_id` CHAR(36),
                `mb_release_group_id` CHAR(36),
                `mb_release_track_id` CHAR(36),
                PRIMARY KEY (`id`),
                FOREIGN KEY(`id`) REFERENCES FILE(`id`)
            );
        ',
        '
            CREATE TABLE `MB_CACHE_ARTIST` (
                `mbid` VARCHAR(36) NOT NULL,
                `name` VARCHAR(128) NOT NULL,
                `image` VARCHAR(8192),
                `json` TEXT NOT NULL,
                PRIMARY KEY (`mbid`)
            );
        ',
        '
            CREATE TABLE `MB_CACHE_RELEASE` (
                `mbid` VARCHAR(36) NOT NULL,
                `title` VARCHAR(512) NOT NULL,
                `year` INTEGER,
                `artist_mbid` VARCHAR(36),
                `artist_name` VARCHAR(128),
                `track_count` INTEGER,
                `json` TEXT NOT NULL,
                PRIMARY KEY (`mbid`)
            );
        ',
        '
            CREATE TABLE `MB_CACHE_TRACK` (
                `mbid` VARCHAR(36) NOT NULL,
                `track` VARCHAR(128) NOT NULL,
                `artist_mbid` VARCHAR(36),
                `artist_name` VARCHAR(128),
                `json` TEXT NOT NULL,
                PRIMARY KEY (`mbid`)
            );
        '
    ),
    2 => array(
        '
            CREATE TABLE `MB_CACHE_ARTIST_RELATION` (
                `artist_mbid` VARCHAR(36) NOT NULL,
                `relation_type_id` VARCHAR(36) NOT NULL,
                `name` VARCHAR(128) NOT NULL,
                `url` VARCHAR(2048) NOT NULL,
                PRIMARY KEY (`artist_mbid`, `relation_type_id`),
                FOREIGN KEY(`artist_mbid`) REFERENCES MB_CACHE_ARTIST (`mbid`)
            );
        '
    ),
    3 => array(
        '
            ALTER TABLE `MB_CACHE_ARTIST` ADD "country"	VARCHAR(36);
        '
    ),
    4 => array(
        '
            CREATE TABLE `MB_CACHE_ARTIST_GENRE` (
                `artist_mbid` VARCHAR(36) NOT NULL,
                `genre` VARCHAR(64) NOT NULL,
                PRIMARY KEY (`artist_mbid`, `genre`),
                FOREIGN KEY(`artist_mbid`) REFERENCES MB_CACHE_ARTIST (`mbid`)
            );
        '
    ),
    5 => array(
        '
            CREATE TABLE `MB_CACHE_RELEASE_TRACK` (
                `release_mbid` VARCHAR(36) NOT NULL,
                `track_mbid` VARCHAR(36) NOT NULL,
                `title` VARCHAR(512) NOT NULL,
                `artist_mbid` VARCHAR(36),
                `artist_name` VARCHAR(128),
                `track_number` INTEGER,
                PRIMARY KEY (`track_mbid`),
                FOREIGN KEY(`release_mbid`) REFERENCES MB_CACHE_RELEASE (`mbid`)
            );
        ',
    ),
    6 => array(
        '
            DROP TABLE `MB_CACHE_TRACK`;
        '
    ),
    7 => array(
        '
            CREATE TABLE `MB_WIKIPEDIA_CACHE_ARTIST` (
                `artist_mbid` VARCHAR(36) NOT NULL,
                `language` VARCHAR(2) NOT NULL,
                `html` TEXT NOT NULL,
                PRIMARY KEY (`artist_mbid`, `language`),
                FOREIGN KEY(`artist_mbid`) REFERENCES MB_CACHE_ARTIST (`mbid`)
            );
        ',
    ),
    8 => array(
        '
            DROP TABLE `MB_CACHE_ARTIST_RELATION`;
        ',
        '
            CREATE TABLE `MB_CACHE_ARTIST_URL_RELATIONSHIP` (
                `artist_mbid` VARCHAR(36) NOT NULL,
                `url_relationship_typeid` VARCHAR(36) NOT NULL,
                `url_relationship_value` VARCHAR(4096) NOT NULL,
                PRIMARY KEY (`artist_mbid`, `url_relationship_typeid`, `url_relationship_value`),
                FOREIGN KEY(`artist_mbid`) REFERENCES MB_CACHE_ARTIST (`mbid`)
            );
        ',
    ),
    9 => array(
        '
            CREATE TABLE `MB_LASTFM_CACHE_ARTIST` (
                `artist_mbid` VARCHAR(36) NOT NULL,
                `bio_summary` TEXT NOT NULL,
                `bio_content` TEXT NOT NULL,
                PRIMARY KEY (`artist_mbid`),
                FOREIGN KEY(`artist_mbid`) REFERENCES MB_CACHE_ARTIST (`mbid`)
            );
        ',
    ),
));
