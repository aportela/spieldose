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
                `name` VARCHAR(32) NOT NULL UNIQUE,
                `ctime` INTEGER NOT NULL,
                `mtime` INTEGER NOT NULL,
                `dtime` INTEGER,
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
    10 => array(
        '
            CREATE TABLE `FILE_PLAYCOUNT_STATS` (
                `file_id` CHAR(36) NOT NULL,
                `user_id` CHAR(36) NOT NULL,
                `play_timestamp` INTEGER NOT NULL,
                FOREIGN KEY(`file_id`) REFERENCES FILE(`id`),
                FOREIGN KEY(`user_id`) REFERENCES USER(`id`),
                PRIMARY KEY(`file_id`, `user_id`, `play_timestamp`)
            );
        '
    ),
    11 => array(
        '
            ALTER TABLE `FILE` ADD "added_timestamp" INTEGER NOT NULL DEFAULT 0;
        '
    ),
    12 => array(
        '
            CREATE TABLE `PLAYLIST` (
                `id` VARCHAR(36) NOT NULL,
                `name` VARCHAR(128) NOT NULL,
                `user_id` VARCHAR(36) NOT NULL,
                `ctime` INTEGER NOT NULL,
                `mtime` INTEGER NOT NULL,
                PRIMARY KEY (`id`),
                FOREIGN KEY(`user_id`) REFERENCES USER (`id`)
            );
        ',
        '
            CREATE TABLE `PLAYLIST_TRACK` (
                `playlist_id` VARCHAR(36) NOT NULL,
                `track_id` VARCHAR(36) NOT NULL,
                `track_index` INTEGER NOT NULL,
                PRIMARY KEY (`playlist_id`, `track_id`),
                FOREIGN KEY(`playlist_id`) REFERENCES PLAYLIST (`id`)
            );
        '
    ),
    13 => array(
        '
            CREATE TABLE `RADIO_STATION` (
                `id` VARCHAR(36) NOT NULL,
                `name` VARCHAR(128) NOT NULL,
                `user_id` VARCHAR(36) NOT NULL,
                `ctime` INTEGER NOT NULL,
                `url` VARCHAR(4096) NOT NULL,
                `url_type` INTEGER NOT NULL,
                `image` VARCHAR(4096) NOT NULL,
                PRIMARY KEY (`id`),
                FOREIGN KEY(`user_id`) REFERENCES USER (`id`)
            );
        '
    ),
    14 => array(
        '
            CREATE TABLE `FILE_FAVORITE` (
                `file_id` VARCHAR(36) NOT NULL,
                `user_id` VARCHAR(36) NOT NULL,
                `favorited` INTEGER NOT NULL,
                PRIMARY KEY (`file_id`, `user_id`),
                FOREIGN KEY(`file_id`) REFERENCES FILE (`id`)
                FOREIGN KEY(`user_id`) REFERENCES USER (`id`)
            );
        '
    ),
    15 => array(
        '
            ALTER TABLE `PLAYLIST` ADD "public"	VARCHAR(1) DEFAULT "N";
        '
    ),
    16 => array(
        '
            CREATE TABLE `LYRICS` (
                `title` VARCHAR(512) NOT NULL,
                `artist` VARCHAR(128) NOT NULL,
                `data` TEXT NOT NULL,
                PRIMARY KEY (`title`, `artist`)
            );
        ',
    ),
    17 => array(
        '
            ALTER TABLE `LYRICS` ADD "source" VARCHAR(32) NOT NULL
        ',
        '
            ALTER TABLE `LYRICS` ADD "ctime" INTEGER NOT NULL;
        ',
        '
            ALTER TABLE `LYRICS` ADD "mtime" INTEGER NOT NULL;
        '
    ),
    18 => array(
        '
            CREATE TABLE `FILE_SCRAP` (
                `file_id` CHAR(36) NOT NULL,
                `scraped` INTEGER NOT NULL,
                FOREIGN KEY(`file_id`) REFERENCES FILE(`id`)
                PRIMARY KEY (`file_id`, `scraped`)
            );
        '
    ),
    19 => array(
        '
            CREATE TABLE `CACHE_ARTIST_MUSICBRAINZ` (
                `mbid` VARCHAR(36) NOT NULL,
                `name` VARCHAR(128) NOT NULL,
                `image` VARCHAR(8192),
                `country` VARCHAR(2),
                `ctime` INTEGER NOT NULL,
                `mtime` INTEGER NOT NULL,
                PRIMARY KEY (`mbid`)
            );
        ',
        '
            CREATE TABLE `CACHE_ARTIST_MUSICBRAINZ_GENRE` (
                `artist_mbid` VARCHAR(36) NOT NULL,
                `genre` VARCHAR(64) NOT NULL,
                FOREIGN KEY(`artist_mbid`) REFERENCES CACHE_ARTIST_MUSICBRAINZ (`mbid`),
                PRIMARY KEY (`artist_mbid`, `genre`)
            );
        ',
        '
            CREATE TABLE `CACHE_ARTIST_MUSICBRAINZ_URL_RELATIONSHIP` (
                `artist_mbid` VARCHAR(36) NOT NULL,
                `relation_type_id` VARCHAR(36) NOT NULL,
                `name` VARCHAR(128) NOT NULL,
                `url` VARCHAR(2048) NOT NULL,
                FOREIGN KEY(`artist_mbid`) REFERENCES CACHE_ARTIST_MUSICBRAINZ (`mbid`),
                PRIMARY KEY (`artist_mbid`, `relation_type_id`, `url`)
            );
        ',
        '
            CREATE TABLE `CACHE_ARTIST_LASTFM` (
                `md5_hash` VARCHAR(32) NOT NULL,
                `mbid` VARCHAR(36),
                `name` VARCHAR(128) NOT NULL,
                `url` VARCHAR(2048) NOT NULL,
                `image` VARCHAR(8192),
                `bio_summary` TEXT,
                `bio_content` TEXT,
                `ctime` INTEGER NOT NULL,
                `mtime` INTEGER NOT NULL,
                PRIMARY KEY (`md5_hash`)
            );
        ',
        '
            CREATE TABLE `CACHE_ARTIST_LASTFM_TAG` (
                `artist_hash` VARCHAR(32) NOT NULL,
                `tag` VARCHAR(64) NOT NULL,
                FOREIGN KEY(`artist_hash`) REFERENCES CACHE_ARTIST_LASTFM (`md5_hash`),
                PRIMARY KEY (`artist_hash`, `tag`)
            );
        ',
        '
            CREATE TABLE `CACHE_ARTIST_LASTFM_SIMILAR` (
                `artist_hash` VARCHAR(32) NOT NULL,
                `name` VARCHAR(128) NOT NULL,
                FOREIGN KEY(`artist_hash`) REFERENCES CACHE_ARTIST_LASTFM (`md5_hash`),
                PRIMARY KEY (`artist_hash`, `name`)
            );
        ',
        '
            CREATE TABLE `CACHE_ARTIST_WIKIPEDIA` (
                `mbid` VARCHAR(36) NOT NULL,
                `intro` TEXT NOT NULL,
                `page` TEXT NOT NULL,
                `ctime` INTEGER NOT NULL,
                `mtime` INTEGER NOT NULL,
                PRIMARY KEY (`mbid`)
            );
        '
    ),
    20 => array(
        '
            CREATE TABLE `CACHE_RELEASE_MUSICBRAINZ` (
                `mbid` VARCHAR(36) NOT NULL,
                `title` VARCHAR(128) NOT NULL,
                `year` INTEGER,
                `media_count` INTEGER NOT NULL,
                `ctime` INTEGER NOT NULL,
                `mtime` INTEGER NOT NULL,
                PRIMARY KEY (`mbid`)
            );
        ',
        '
            CREATE TABLE `CACHE_RELEASE_MUSICBRAINZ_MEDIA` (
                `release_mbid` VARCHAR(36) NOT NULL,
                `position` INTEGER NOT NULL,
                `track_count` INTEGER NOT NULL,
                FOREIGN KEY(`release_mbid`) REFERENCES CACHE_RELEASE_MUSICBRAINZ (`mbid`),
                PRIMARY KEY (`release_mbid`, `position`)
            );
        ',
        '
            CREATE TABLE `CACHE_RELEASE_MUSICBRAINZ_MEDIA_TRACK` (
                `mbid` VARCHAR(36) NOT NULL,
                `release_mbid` VARCHAR(36) NOT NULL,
                `release_media` INTEGER NOT NULL,
                `position` INTEGER NOT NULL,
                `title` VARCHAR(128) NOT NULL,
                `artist_mbid` VARCHAR(36) NOT NULL,
                `artist_name` VARCHAR(128) NOT NULL,
                FOREIGN KEY(`mbid`) REFERENCES CACHE_RELEASE_MUSICBRAINZ (`mbid`),
                PRIMARY KEY (`mbid`, `release_mbid`, `release_media`, `position`)
            );
        '
    ),
    21 => array(
        '
            DROP TABLE `FILE_SCRAP`;
        '
    ),
    22 => array(
        '
            CREATE TABLE `SCANNER_DIRECTORY` (
                `id` CHAR(36) NOT NULL,
                `path` VARCHAR(4096) NOT NULL UNIQUE,
                `ctime` INTEGER NOT NULL,
                `atime` INTEGER NOT NULL,
                PRIMARY KEY (`id`)
            );
        ',
    ),
    23 => array(
        // TODO: not null
        '
            ALTER TABLE `CACHE_RELEASE_MUSICBRAINZ` ADD `artist_mbid` VARCHAR(36);
        ',
        '
            ALTER TABLE `CACHE_RELEASE_MUSICBRAINZ` ADD `artist_name` VARCHAR(128);
        '
    )
));
