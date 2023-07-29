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
    )
));
