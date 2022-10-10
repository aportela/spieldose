<?php

    return
    (
        array
        (
            1 => array (
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
            )
        )
    );

?>