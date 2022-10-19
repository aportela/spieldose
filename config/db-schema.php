<?php

return (array(
    1 => array(
        " CREATE TABLE IF NOT EXISTS FILES (ID CHAR(40) PRIMARY KEY, DIRECTORY_ID CHAR(40) NOT NULL, NAME VARCHAR(255) NOT NULL, ATIME INTEGER NOT NULL, MTIME INTEGER NOT NULL); ",
    ),
    2 => array(
        " CREATE TABLE IF NOT EXISTS FILE_ID3_TAG(ID CHAR(40) PRIMARY KEY, TITLE VARCHAR(128), ARTIST VARCHAR(128), ALBUM_ARTIST VARCHAR(128), ALBUM VARCHAR(128), YEAR CHAR(4), TRACK_NUMBER INT, DISC_NUMBER INT, PLAYTIME_SECONDS INT, MIME VARCHAR(127), MB_ARTIST_ID CHAR(36), MB_ALBUM_ARTIST_ID CHAR(36), MB_ALBUM_ID CHAR(36)); "
    ),
    3 => array(
        " CREATE TABLE IF NOT EXISTS DIRECTORIES(ID CHAR(40) PRIMARY KEY, PATH VARCHAR(4096) NOT NULL, ATIME INTEGER NOT NULL, MTIME INTEGER NOT NULL, COVER_FILENAME VARCHAR(4096)); "
    ),
    4 => array(
        "
            CREATE TABLE [USER] (
                [id] VARCHAR(36) UNIQUE NOT NULL PRIMARY KEY,
                [email] VARCHAR(255) UNIQUE NOT NULL,
                [password_hash] VARCHAR(60) NOT NULL
            );
        "
    ),
    5 => array(
        "
            CREATE TABLE [PLAY_STATS] (
                [USER] VARCHAR(36) NOT NULL,
                [FILE] VARCHAR(40) NOT NULL,
                [PLAYED] INTEGER NOT NULL,
                PRIMARY KEY(`USER`,`FILE`, `PLAYED`)
            );
        "
    ),
    6 => array(
        "
            CREATE TABLE [LOVED_FILE] (
                [FILE] VARCHAR(40) NOT NULL,
                [USER] VARCHAR(36) NOT NULL,
                [LOVED]	INTEGER NOT NULL DEFAULT 0,
                PRIMARY KEY([FILE], [USER])
            );
        "
    ),
    7 => array(
        "
            ALTER TABLE FILE_ID3_TAG ADD COLUMN [GENRE] VARCHAR(128)
        "
    ),
    8 => array(
        "
            CREATE TABLE [MB_CACHE_ARTIST] (
                [MBID] VARCHAR(36) UNIQUE NOT NULL PRIMARY KEY,
                [ARTIST] VARCHAR(128) NOT NULL,
                [IMAGE] VARCHAR(8192),
                [BIO] TEXT,
                [JSON] TEXT NOT NULL
            );
        ",
        "
            CREATE TABLE [MB_CACHE_ALBUM] (
                [MBID] VARCHAR(36) UNIQUE NOT NULL PRIMARY KEY,
                [ALBUM] VARCHAR(128) NOT NULL,
                [ARTIST] VARCHAR(36) NOT NULL,
                [IMAGE] VARCHAR(8192),
                [YEAR] INTEGER,
                [JSON] TEXT NOT NULL
            );
        ",
        "
            CREATE TABLE [MB_CACHE_TRACK] (
                [MBID] VARCHAR(36) UNIQUE NOT NULL PRIMARY KEY,
                [TRACK] VARCHAR(128) NOT NULL,
                [ARTIST_MBID] VARCHAR(36) NOT NULL,
                [ARTIST_MBNAME] VARCHAR(36) NOT NULL,
                [JSON] TEXT NOT NULL
            );
        "
    ),
    9 => array(
        "
            CREATE TABLE [USER2] (
                [ID] VARCHAR(36) UNIQUE NOT NULL PRIMARY KEY,
                [EMAIL] VARCHAR(255) UNIQUE NOT NULL,
                [PASSWORD_HASH] VARCHAR(60) NOT NULL
            );
        ",
        "
            INSERT INTO USER2 SELECT * FROM USER;
        ",
        "
            DROP TABLE USER;
        ",
        "
            ALTER TABLE USER2 RENAME TO USER
        "
    )
));
