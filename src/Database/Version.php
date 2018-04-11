<?php

      declare(strict_types=1);

      namespace Spieldose\Database;

      class Version {

        private $dbh;
        private $databaseType;

        private $installQueries = array(
            "PDO_SQLITE" => array(
                '
                    CREATE TABLE [VERSION] (
                        [num]	NUMERIC NOT NULL UNIQUE,
                        [date]	INTEGER NOT NULL,
                        PRIMARY KEY([num])
                    );
                ',
                '
                    INSERT INTO VERSION VALUES ("1.00", current_timestamp);
                ',
                '
                    PRAGMA journal_mode=WAL;
                '
            ),
            "PDO_MARIADB" => array(
                    '
                    CREATE TABLE `VERSION` (
                        `num`	FLOAT NOT NULL,
                        `date`	TIMESTAMP NOT NULL,
                        PRIMARY KEY(`num`)
                    );
                ',
                '
                    INSERT INTO `VERSION` VALUES (1.00, current_timestamp);
                '
            )
        );

        private $upgradeQueries = array(
            "PDO_SQLITE" => array(
                "1.01" => array(
                    '
                        CREATE TABLE [USER] (
                            [id] VARCHAR(36) UNIQUE NOT NULL PRIMARY KEY,
                            [email] VARCHAR(255) UNIQUE NOT NULL,
                            [password_hash] VARCHAR(60) NOT NULL
                        );
                    ',
                    '
                        CREATE TABLE [FILE] (
                            [id] VARCHAR(40) UNIQUE NOT NULL PRIMARY KEY,
                            [local_path] VARCHAR(2048) UNIQUE NOT NULL,
                            [base_path] VARCHAR(2048) NOT NULL,
                            [file_name] VARCHAR(512) NOT NULL,
                            [mime] VARCHAR(127),
                            [track_name] VARCHAR(128),
                            [track_mbid] VARCHAR(36),
                            [track_artist] VARCHAR(128),
                            [artist_mbid] VARCHAR(36),
                            [album_name] VARCHAR(128),
                            [album_mbid] VARCHAR(36),
                            [album_artist] VARCHAR(128),
                            [album_artist_mbid] VARCHAR(36),
                            [disc_number] INTEGER,
                            [track_number] VARCHAR(128),
                            [year] INTEGER,
                            [genre] VARCHAR(128),
                            [playtime_seconds] INTEGER,
                            [playtime_string] VARCHAR(16),
                            [image] VARCHAR(8192),
                            [created] INTEGER NOT NULL
                        );
                    ',
                    '
                        CREATE TABLE [MB_CACHE_ARTIST] (
                            [mbid] VARCHAR(36) UNIQUE NOT NULL PRIMARY KEY,
                            [artist] VARCHAR(128) NOT NULL,
                            [image] VARCHAR(8192),
                            [bio] TEXT,
                            [json] TEXT NOT NULL
                        );
                    ',
                    '
                        CREATE TABLE [MB_CACHE_ALBUM] (
                            [mbid] VARCHAR(36) UNIQUE NOT NULL PRIMARY KEY,
                            [album] VARCHAR(128) NOT NULL,
                            [artist] VARCHAR(36) NOT NULL,
                            [image] VARCHAR(8192),
                            [year] INTEGER,
                            [json] TEXT NOT NULL
                        );
                    ',
                    '
                        CREATE TABLE [MB_CACHE_TRACK] (
                            [mbid] VARCHAR(36) UNIQUE NOT NULL PRIMARY KEY,
                            [track] VARCHAR(128) NOT NULL,
                            [artist_mbid] VARCHAR(36) NOT NULL,
                            [artist_mbname] VARCHAR(36) NOT NULL,
                            [json] TEXT NOT NULL
                        );
                    '
                ),
                "1.02" => array(
                    '
                        CREATE TABLE [STATS] (
                            [user_id] VARCHAR(36) NOT NULL,
                            [file_id] VARCHAR(40) NOT NULL,
                            [played] INTEGER NOT NULL,
                            PRIMARY KEY(`user_id`,`file_id`, `played`)
                        );
                    ',
                    '
                        CREATE TABLE [LOVED_FILE] (
                            [file_id] VARCHAR(40) NOT NULL,
                            [user_id] VARCHAR(36) NOT NULL,
                            [loved]	INTEGER NOT NULL DEFAULT 0,
                            PRIMARY KEY([file_id], [user_id])
                        );
                    '
                ),
                "1.03" => array(
                    '
                        CREATE TABLE [PLAYLIST] (
                            [id] VARCHAR(36) UNIQUE NOT NULL PRIMARY KEY,
                            [user_id] VARCHAR(36) NOT NULL,
                            [name] VARCHAR(32) NOT NULL
                        );
                    ',
                    '
                        CREATE TABLE [PLAYLIST_TRACK] (
                            [playlist_id] VARCHAR(36) NOT NULL,
                            [file_id] VARCHAR(40) NOT NULL,
                            PRIMARY KEY([playlist_id], [file_id])
                        );
                    ',
                ),
                "1.04" => array(
                    '
                        CREATE TABLE `LOCAL_PATH_ALBUM_COVER` (
                            [id] VARCHAR(36) UNIQUE NOT NULL PRIMARY KEY,
                            [base_path] VARCHAR(2048) UNIQUE NOT NULL,
                            [file_name] VARCHAR(512) NOT NULL
                        );
                    '
                )
            ),
            "PDO_MARIADB" => array(
                "1.01" => array(
                    '
                        CREATE TABLE `USER` (
                            `id` VARCHAR(36) UNIQUE NOT NULL PRIMARY KEY,
                            `email` VARCHAR(255) UNIQUE NOT NULL,
                            `password_hash` VARCHAR(60) NOT NULL
                        );
                    ',
                    '
                        CREATE TABLE `FILE` (
                            `id` VARCHAR(40) UNIQUE NOT NULL PRIMARY KEY,
                            `local_path` VARCHAR(2048) NOT NULL,
                            `base_path` VARCHAR(2048) NOT NULL,
                            `file_name` VARCHAR(512) NOT NULL,
                            `mime` VARCHAR(127),
                            `track_name` VARCHAR(128),
                            `track_mbid` VARCHAR(36),
                            `track_artist` VARCHAR(128),
                            `artist_mbid` VARCHAR(36),
                            `album_name` VARCHAR(128),
                            `album_mbid` VARCHAR(36),
                            `album_artist` VARCHAR(128),
                            `album_artist_mbid` VARCHAR(36),
                            `disc_number` INTEGER,
                            `track_number` VARCHAR(128),
                            `year` INTEGER,
                            `genre` VARCHAR(128),
                            `playtime_seconds` INTEGER,
                            `playtime_string` VARCHAR(16),
                            `image` VARCHAR(8192),
                            `created` INTEGER NOT NULL
                        );
                    ',
                    '
                        CREATE TABLE `MB_CACHE_ARTIST` (
                            `mbid` VARCHAR(36) UNIQUE NOT NULL PRIMARY KEY,
                            `artist` VARCHAR(128) NOT NULL,
                            `image` VARCHAR(8192),
                            `bio` TEXT,
                            `json` TEXT NOT NULL
                        );
                    ',
                    '
                        CREATE TABLE `MB_CACHE_ALBUM` (
                            `mbid` VARCHAR(36) UNIQUE NOT NULL PRIMARY KEY,
                            `album` VARCHAR(128) NOT NULL,
                            `artist` VARCHAR(36) NOT NULL,
                            `image` VARCHAR(8192),
                            `year` INTEGER,
                            `json` TEXT NOT NULL
                        );
                    ',
                    '
                        CREATE TABLE `MB_CACHE_TRACK` (
                            `mbid` VARCHAR(36) UNIQUE NOT NULL PRIMARY KEY,
                            `track` VARCHAR(128) NOT NULL,
                            `artist_mbid` VARCHAR(36) NOT NULL,
                            `artist_mbname` VARCHAR(36) NOT NULL,
                            `json` TEXT NOT NULL
                        );
                    '
                ),
                "1.02" => array(
                    '
                        CREATE TABLE `STATS` (
                            `user_id` VARCHAR(36) NOT NULL,
                            `file_id` VARCHAR(40) NOT NULL,
                            `played` INTEGER NOT NULL,
                            PRIMARY KEY(`user_id`,`file_id`, `played`)
                        );
                    ',
                    '
                        CREATE TABLE `LOVED_FILE` (
                            `file_id` VARCHAR(40) NOT NULL,
                            `user_id` VARCHAR(36) NOT NULL,
                            `loved`	INTEGER NOT NULL DEFAULT 0,
                            PRIMARY KEY(`file_id`, `user_id`)
                        );
                    '
                ),
                "1.03" => array(
                    '
                        CREATE TABLE `PLAYLIST` (
                            `id` VARCHAR(36) UNIQUE NOT NULL PRIMARY KEY,
                            `user_id` VARCHAR(36) NOT NULL,
                            `name` VARCHAR(32) NOT NULL
                        );
                    ',
                    '
                        CREATE TABLE `PLAYLIST_TRACK` (
                            `playlist_id` VARCHAR(36) NOT NULL,
                            `file_id` VARCHAR(40) NOT NULL,
                            PRIMARY KEY(`playlist_id`, `file_id`)
                        );
                    ',
                ),
                "1.04" => array(
                    '
                        CREATE TABLE `LOCAL_PATH_ALBUM_COVER` (
                            `id` VARCHAR(36) UNIQUE NOT NULL PRIMARY KEY,
                            `base_path` VARCHAR(2048) UNIQUE NOT NULL,
                            `file_name` VARCHAR(512) NOT NULL
                        );
                    '
                )
            )
        );

        public function __construct (\Spieldose\Database\DB $dbh, string $databaseType) {
            $this->dbh = $dbh;
            $this->databaseType = $databaseType;
        }

        public function __destruct() { }

        public function get() {
            $query = ' SELECT num FROM VERSION ORDER BY num DESC LIMIT 1; ';
            $results = $this->dbh->query($query, array());
            if ($results && count($results) == 1) {
                return($results[0]->num);
            } else {
                throw new \Spieldose\Exception\NotFoundException("invalid database version");
            }
        }

        private function set(float $number) {
            $params = array(
                (new \Spieldose\Database\DBParam())->str(":num", (string) $number)
            );
            $query = '
                INSERT INTO VERSION
                    (num, date)
                VALUES
                    (:num, current_timestamp);
            ';
            return($this->dbh->execute($query, $params));
        }

        public function install() {
            if (isset($this->installQueries[$this->databaseType])) {
                foreach($this->installQueries[$this->databaseType] as $query) {
                    $this->dbh->execute($query);
                }
            } else {
                throw new \Exception("Unsupported database type: " . $this->databaseType);
            }
        }

        public function upgrade() {
            if (isset($this->upgradeQueries[$this->databaseType])) {
                $result = array(
                    "successVersions" => array(),
                    "failedVersions" => array()
                );
                $actualVersion = $this->get();
                $errors = false;
                foreach($this->upgradeQueries[$this->databaseType] as $version => $queries) {
                    if (! $errors && $version > $actualVersion) {
                        try {
                            $this->dbh->beginTransaction();
                            foreach($queries as $query) {
                                $this->dbh->execute($query);
                            }
                            $this->set(floatval($version));
                            $this->dbh->commit();
                            $result["successVersions"][] = $version;
                        } catch (\PDOException $e) {
                            echo $e->getMessage();
                            $this->dbh->rollBack();
                            $errors = true;
                            $result["failedVersions"][] = $version;
                        }
                    } else if ($errors) {
                        $result["failedVersions"][] = $version;
                    }
                }
                return($result);
            } else {
                throw new \Exception("Unsupported database type: " . $this->databaseType);
            }
        }

        public function hasUpgradeAvailable() {
            $actualVersion = $this->get();
            $errors = false;
            foreach($this->upgradeQueries[$this->databaseType] as $version => $queries) {
                if ($version > $actualVersion) {
                    return(true);
                }
            }
            return(false);
        }

    }

?>