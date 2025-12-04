<?php

return ([
    2 => [
        '
            CREATE TABLE USER (
                id TEXT NOT NULL CHECK(length(id) == 36),
                email TEXT NOT NULL UNIQUE CHECK(length(email) <= 255),
                password_hash TEXT NOT NULL CHECK(length(password_hash) <= 60),
                ctime INTEGER NOT NULL,
                mtime INTEGER,
                PRIMARY KEY (id)
            ) STRICT;

            CREATE TABLE LIBRARY_PATH (
                id TEXT NOT NULL CHECK(length(id) == 36),
                path TEXT NOT NULL UNIQUE CHECK(length(path) <= 4096),
                name TEXT NOT NULL UNIQUE CHECK(length(path) <= 128),
                ctime INTEGER NOT NULL,
                mtime INTEGER NOT NULL,
                PRIMARY KEY (`id`)
            ) STRICT;

            CREATE TABLE DIRECTORY (
                id TEXT NOT NULL CHECK(length(id) == 36),
                library_path_id TEXT NOT NULL CHECK(length(library_path_id) == 36),
                path TEXT NOT NULL UNIQUE CHECK(length(path) <= 4096),
                cover_filename TEXT CHECK(length(path) <= 4096),
                ctime INTEGER NOT NULL,
                mtime INTEGER NOT NULL,
                PRIMARY KEY (id),
                FOREIGN KEY(library_path_id) REFERENCES LIBRARY_PATH(id) ON DELETE CASCADE
            ) STRICT;

            CREATE TABLE FILE (
                id TEXT NOT NULL CHECK(length(id) == 36),
                directory_id TEXT NOT NULL CHECK(length(directory_id) == 36),
                name TEXT NOT NULL CHECK(length(directory_id) <= 255),
                size INTEGER NOT NULL,
                ctime INTEGER NOT NULL,
                mtime INTEGER NOT NULL,
                PRIMARY KEY (id),
                FOREIGN KEY(directory_id) REFERENCES DIRECTORY(id) ON DELETE CASCADE
            ) STRICT;

            CREATE TABLE FILE_PLAYCOUNT_STATS (
                file_id TEXT NOT NULL CHECK(length(file_id) == 36),
                user_id TEXT NOT NULL CHECK(length(user_id) == 36),
                ptime INTEGER NOT NULL,
                PRIMARY KEY(file_id, user_id, ptime),
                FOREIGN KEY(file_id) REFERENCES FILE(id) ON DELETE CASCADE,
                FOREIGN KEY(user_id) REFERENCES USER(id) ON DELETE CASCADE
            ) STRICT;

            CREATE TABLE FILE_FAVORITE (
                file_id TEXT NOT NULL CHECK(length(file_id) == 36),
                user_id TEXT NOT NULL CHECK(length(user_id) == 36),
                ftime INTEGER NOT NULL,
                PRIMARY KEY(file_id, user_id),
                FOREIGN KEY(file_id) REFERENCES FILE(id) ON DELETE CASCADE,
                FOREIGN KEY(user_id) REFERENCES USER(id) ON DELETE CASCADE
            ) STRICT;

            CREATE TABLE FILE_ID3_TAG (
                file_id TEXT NOT NULL CHECK(length(file_id) == 36),
                title TEXT CHECK(length(title) <= 256),
                artist TEXT CHECK(length(artist) <= 128),
                album_artist TEXT CHECK(length(album_artist) <= 128),
                album TEXT CHECK(length(album) <= 128),
                year INT,
                original_year INT,
                genre TEXT CHECK(length(genre) <= 128),
                track_number INT,
                disc_number INT,
                playtime_seconds INT,
                mime TEXT CHECK(length(mime) <= 128),
                release_group_mbid TEXT CHECK(length(release_group_mbid) == 36),
                release_mbid TEXT CHECK(length(release_mbid) == 36),
                release_track_mbid TEXT CHECK(length(release_track_mbid) == 36),
                PRIMARY KEY (file_id),
                FOREIGN KEY(file_id) REFERENCES FILE(id) ON DELETE CASCADE
            ) STRICT;

            CREATE TABLE FILE_ID3_TAG_MUSICBRAINZ_ARTIST (
                file_id TEXT NOT NULL CHECK(length(file_id) == 36),
                artist_mbid TEXT CHECK(length(artist_mbid) == 36),
                PRIMARY KEY (file_id, artist_mbid),
                FOREIGN KEY(file_id) REFERENCES FILE(id) ON DELETE CASCADE
            ) STRICT;

            CREATE TABLE FILE_ID3_TAG_MUSICBRAINZ_RELEASE_ARTIST (
                file_id TEXT NOT NULL CHECK(length(file_id) == 36),
                artist_mbid TEXT CHECK(length(artist_mbid) == 36),
                PRIMARY KEY (file_id, artist_mbid),
                FOREIGN KEY(file_id) REFERENCES FILE(id) ON DELETE CASCADE
            ) STRICT;

            CREATE TABLE QUEUE_FILE_ID3_SCAN (
                file_id TEXT NOT NULL CHECK(length(file_id) == 36),
                ctime INTEGER NOT NULL,
                PRIMARY KEY (file_id),
                FOREIGN KEY(file_id) REFERENCES FILE(id) ON DELETE CASCADE
            ) STRICT;

            CREATE TABLE CACHE_MUSICBRAINZ_ARTIST (
                mbid TEXT NOT NULL CHECK(length(mbid) == 36),
                name TEXT NOT NULL CHECK(length(name) <= 128),
                country TEXT CHECK(length(country) == 2),
                ctime INTEGER NOT NULL,
                mtime INTEGER,
                PRIMARY KEY (mbid)
            ) STRICT;

            CREATE TABLE CACHE_MUSICBRAINZ_ARTIST_GENRE (
                artist_mbid TEXT NOT NULL CHECK(length(artist_mbid) == 36),
                genre TEXT NOT NULL CHECK(length(artist_mbid) <= 64),
                PRIMARY KEY (artist_mbid, genre),
                FOREIGN KEY(artist_mbid) REFERENCES CACHE_MUSICBRAINZ_ARTIST (mbid) ON DELETE CASCADE
            ) STRICT;

            CREATE TABLE CACHE_MUSICBRAINZ_ARTIST_URL_RELATIONSHIP (
                artist_mbid TEXT NOT NULL CHECK(length(artist_mbid) == 36),
                relation_type_id TEXT NOT NULL CHECK(length(relation_type_id) == 36),
                name TEXT NOT NULL CHECK(length(name) <= 128),
                url TEXT NOT NULL CHECK(length(url) <= 2048),
                PRIMARY KEY (artist_mbid, relation_type_id, url),
                FOREIGN KEY(artist_mbid) REFERENCES CACHE_MUSICBRAINZ_ARTIST (mbid) ON DELETE CASCADE
            ) STRICT;

            CREATE TABLE CACHE_MUSICBRAINZ_RECORDING (
                mbid TEXT NOT NULL CHECK(length(mbid) == 36),
                title TEXT NOT NULL CHECK(length(title) <= 256),
                ctime INTEGER NOT NULL,
                mtime INTEGER,
                PRIMARY KEY (mbid)
            ) STRICT;

            CREATE TABLE CACHE_MUSICBRAINZ_RECORDING_ARTIST (
                recording_mbid TEXT NOT NULL CHECK(length(recording_mbid) == 36),
                artist_mbid TEXT NOT NULL CHECK(length(artist_mbid) == 36),
                PRIMARY KEY (recording_mbid, artist_mbid),
                FOREIGN KEY(recording_mbid) REFERENCES CACHE_MUSICBRAINZ_RECORDING (mbid) ON DELETE CASCADE,
                FOREIGN KEY(artist_mbid) REFERENCES CACHE_MUSICBRAINZ_ARTIST (mbid) ON DELETE CASCADE
            ) STRICT;

            CREATE TABLE CACHE_MUSICBRAINZ_RELEASE (
                mbid TEXT NOT NULL CHECK(length(mbid) == 36),
                title TEXT NOT NULL CHECK(length(title) <= 128),
                year INT,
                ctime INTEGER NOT NULL,
                mtime INTEGER,
                PRIMARY KEY (mbid)
            ) STRICT;

            CREATE TABLE CACHE_MUSICBRAINZ_RELEASE_ARTIST (
                release_mbid TEXT NOT NULL CHECK(length(release_mbid) == 36),
                artist_mbid TEXT NOT NULL CHECK(length(artist_mbid) == 36),
                PRIMARY KEY (release_mbid, artist_mbid),
                FOREIGN KEY(release_mbid) REFERENCES CACHE_MUSICBRAINZ_RELEASE (`mbid`) ON DELETE CASCADE
            ) STRICT;

            CREATE TABLE CACHE_MUSICBRAINZ_MEDIA (
                mbid TEXT NOT NULL CHECK(length(mbid) == 36),
                release_mbid TEXT NOT NULL CHECK(length(release_mbid) == 36),
                position INTEGER,
                ctime INTEGER NOT NULL,
                mtime INTEGER,
                PRIMARY KEY (mbid),
                FOREIGN KEY(release_mbid) REFERENCES CACHE_MUSICBRAINZ_RELEASE (`mbid`) ON DELETE CASCADE
            ) STRICT;

            CREATE TABLE CACHE_MUSICBRAINZ_TRACK (
                mbid TEXT NOT NULL CHECK(length(mbid) == 36),
                media_mbid TEXT NOT NULL CHECK(length(media_mbid) == 36),
                recording_mbid TEXT NOT NULL CHECK(length(recording_mbid) == 36),
                position INTEGER,
                ctime INTEGER NOT NULL,
                mtime INTEGER,
                PRIMARY KEY (mbid),
                FOREIGN KEY(media_mbid) REFERENCES CACHE_MUSICBRAINZ_MEDIA (mbid) ON DELETE CASCADE,
                FOREIGN KEY(recording_mbid) REFERENCES CACHE_MUSICBRAINZ_RECORDING (mbid) ON DELETE CASCADE
            ) STRICT;

            CREATE TABLE CACHE_LASTFM_ARTIST (
                md5_hash TEXT NOT NULL CHECK(length(md5_hash) == 32),
                mbid TEXT CHECK(length(mbid) == 36),
                name TEXT NOT NULL CHECK(length(name) <= 128),
                url TEXT NOT NULL CHECK(length(url) <= 2048),
                image TEXT CHECK(length(image) <= 8192),
                bio_summary TEXT,
                bio_content TEXT,
                ctime INTEGER NOT NULL,
                mtime INTEGER,
                PRIMARY KEY (md5_hash)
            ) STRICT;

            CREATE TABLE CACHE_LASTFM_ARTIST_TAG (
                artist_hash TEXT NOT NULL CHECK(length(artist_hash) == 32),
                tag TEXT NOT NULL CHECK(length(tag) <= 64),
                PRIMARY KEY (artist_hash, tag),
                FOREIGN KEY(artist_hash) REFERENCES CACHE_LASTFM_ARTIST (md5_hash) ON DELETE CASCADE
            ) STRICT;

            CREATE TABLE CACHE_LASTFM_ARTIST_SIMILAR (
                artist_hash TEXT NOT NULL CHECK(length(artist_hash) == 32),
                name TEXT NOT NULL CHECK(length(name) <= 128),
                PRIMARY KEY (artist_hash, name),
                FOREIGN KEY(artist_hash) REFERENCES CACHE_LASTFM_ARTIST (md5_hash) ON DELETE CASCADE
            ) STRICT;

            CREATE TABLE CACHE_LASTFM_ALBUM (
                md5_hash TEXT NOT NULL CHECK(length(md5_hash) == 32),
                mbid TEXT CHECK(length(mbid) == 36),
                name TEXT NOT NULL CHECK(length(name) <= 128),
                artist_name TEXT NOT NULL CHECK(length(artist_name) <= 128),
                url TEXT NOT NULL CHECK(length(url) <= 2048),
                wiki_summary TEXT,
                wiki_content TEXT,
                ctime INTEGER NOT NULL,
                mtime INTEGER,
                PRIMARY KEY (md5_hash)
            ) STRICT;

            CREATE TABLE CACHE_LASTFM_ALBUM_TAG (
                album_hash TEXT NOT NULL CHECK(length(album_hash) == 32),
                tag TEXT NOT NULL CHECK(length(tag) <= 64),
                PRIMARY KEY (album_hash, tag),
                FOREIGN KEY(album_hash) REFERENCES CACHE_LASTFM_ALBUM (md5_hash) ON DELETE CASCADE
            ) STRICT;

            CREATE TABLE CACHE_LASTFM_ALBUM_TRACK (
                md5_hash TEXT NOT NULL CHECK(length(md5_hash) == 32),
                album_hash TEXT NOT NULL CHECK(length(album_hash) == 32),
                name TEXT NOT NULL CHECK(length(name) <= 128),
                artist_name TEXT NOT NULL CHECK(length(artist_name) <= 128),
                rank INTEGER NOT NULL,
                PRIMARY KEY (md5_hash),
                FOREIGN KEY(album_hash) REFERENCES CACHE_LASTFM_ALBUM (md5_hash) ON DELETE CASCADE
            ) STRICT;

            CREATE TABLE CACHE_ARTIST_WIKIPEDIA (
                artist_mbid TEXT NOT NULL CHECK(length(artist_mbid) == 36),
                artist_name TEXT NOT NULL CHECK(length(artist_name) <= 128),
                language TEXT NOT NULL CHECK(length(language) == 2),
                html TEXT NOT NULL,
                ctime INTEGER NOT NULL,
                mtime INTEGER,
                PRIMARY KEY (artist_mbid, artist_name, language),
                FOREIGN KEY(artist_mbid) REFERENCES CACHE_MUSICBRAINZ_ARTIST (`mbid`) ON DELETE CASCADE
            ) STRICT;

            CREATE TABLE CACHE_LYRICS (
                title TEXT NOT NULL CHECK(length(title) <= 256),
                artist TEXT NOT NULL CHECK(length(artist) <= 128),
                lyrics TEXT NOT NULL,
                source TEXT NOT NULL CHECK(length(source) <= 32),
                ctime INTEGER NOT NULL,
                mtime INTEGER,
                PRIMARY KEY (title, artist)
            ) STRICT;

            CREATE TABLE PLAYLIST (
                id TEXT NOT NULL CHECK(length(id) == 36),
                name TEXT NOT NULL CHECK(length(name) <= 128),
                user_id TEXT NOT NULL CHECK(length(user_id) == 36),
                ctime INTEGER NOT NULL,
                mtime INTEGER,
                PRIMARY KEY (id),
                FOREIGN KEY(user_id) REFERENCES USER(id) ON DELETE CASCADE
            ) STRICT;

            CREATE TABLE PLAYLIST_FILE (
                playlist_id TEXT NOT NULL CHECK(length(playlist_id) == 36),
                file_id TEXT NOT NULL CHECK(length(file_id) == 36),
                file_index INTEGER NOT NULL,
                PRIMARY KEY (playlist_id, file_id, file_index),
                FOREIGN KEY(playlist_id) REFERENCES PLAYLIST(id) ON DELETE CASCADE,
                FOREIGN KEY(file_id) REFERENCES FILE(id) ON DELETE CASCADE
            ) STRICT;
        ',
    ]
]);
/*
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
                PRIMARY KEY (`playlist_id`, `track_id`, `track_index`),
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
    15 => array(
        '
            ALTER TABLE `PLAYLIST` ADD "public"	VARCHAR(1) DEFAULT "N";
        '
    ),
    24 => array(
        '
            CREATE TABLE `CURRENT_PLAYLIST` (
                `id` VARCHAR(36) NOT NULL,
                `ctime` INTEGER NOT NULL,
                `mtime` INTEGER NOT NULL,
                `current_index` INTEGER NOT NULL DEFAULT 0,
                `radiostation_id` VARCHAR(36),
                PRIMARY KEY (`id`),
                FOREIGN KEY(`id`) REFERENCES USER (`id`)
            );
        ',
        '
            CREATE TABLE `CURRENT_PLAYLIST_TRACK` (
                `playlist_id` VARCHAR(36) NOT NULL,
                `track_id` VARCHAR(36) NOT NULL,
                `track_index` INTEGER NOT NULL,
                `track_shuffled_index` INTEGER NOT NULL,
                PRIMARY KEY (`playlist_id`, `track_id`, `track_index`),
                FOREIGN KEY(`playlist_id`) REFERENCES CURRENT_PLAYLIST (`id`)
            );
        '
    ),
    25 => array(
        '
            ALTER TABLE `CURRENT_PLAYLIST` ADD `playlist_id` VARCHAR(36);
        ',
    ),
    26 => array(
        '
            DROP TABLE `RADIO_STATION`;
        '
    ),
    27 => array(
        '
            ALTER TABLE `CACHE_RELEASE_MUSICBRAINZ_MEDIA_TRACK` ADD `length` INTEGER NOT NULL
        '
    )
));
*/