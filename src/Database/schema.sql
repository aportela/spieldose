<?php

    return
    (
        array
        (
            1 => array (
                " CREATE TABLE IF NOT EXISTS FILES (SHA1_HASH CHAR(40) PRIMARY KEY, PATH VARCHAR(4096) NOT NULL, NAME VARCHAR(255) NOT NULL, ATIME INTEGER NOT NULL, MTIME INTEGER NOT NULL); ",
            )
        )
    );

?>