<?php

    declare(strict_types=1);

    namespace Spieldose;

    class Scrapper {

        private $dbh = null;
        private $id3 = null;

	    public function __construct (\Spieldose\Database\DB $dbh) {
            $this->dbh = $dbh;
            $this->id3 = new \Spieldose\ID3();
        }

        public function __destruct() { }

        public function scrapFileTags(string $filePath = "") {
            if (file_exists($filePath)) {
                $this->id3->analyze($filePath);
                $params = array();
                $params[] = (new \Spieldose\Database\DBParam())->str(":id", sha1($filePath));
                $params[] = (new \Spieldose\Database\DBParam())->str(":local_path", $filePath);
                $params[] = (new \Spieldose\Database\DBParam())->str(":base_path", dirname($filePath));
                $params[] = (new \Spieldose\Database\DBParam())->str(":file_name", basename($filePath));
                $trackName = $this->id3->getTrackTitle();
                if (! empty($trackName)) {
                    $params[] = (new \Spieldose\Database\DBParam())->str(":track_name", $trackName);
                } else {
                    $params[] = (new \Spieldose\Database\DBParam())->null(":track_name");
                }
                $trackArtist = $this->id3->getTrackArtistName();
                if (! empty($trackArtist)) {
                    $params[] = (new \Spieldose\Database\DBParam())->str(":track_artist", $trackArtist);
                } else {
                    $params[] = (new \Spieldose\Database\DBParam())->null(":track_artist");
                }
                $artistMBId = $this->id3->getMusicBrainzArtistId();
                // multiple mbids (divided by "/") not supported
                if (! empty($artistMBId) && strlen($artistMBId) == 36) {
                    $params[] = (new \Spieldose\Database\DBParam())->str(":artist_mbid", $artistMBId);
                } else {
                    $params[] = (new \Spieldose\Database\DBParam())->null(":artist_mbid");
                }
                $trackAlbum = $this->id3->getAlbum();
                if (! empty($trackAlbum)) {
                    $params[] = (new \Spieldose\Database\DBParam())->str(":album_name", $trackAlbum);
                } else {
                    $params[] = (new \Spieldose\Database\DBParam())->null(":album_name");
                }
                $albumMBId = $this->id3->getMusicBrainzAlbumId();
                // multiple mbids (divided by "/") not supported
                if (! empty($albumMBId) && strlen($albumMBId) == 36) {
                    $params[] = (new \Spieldose\Database\DBParam())->str(":album_mbid", $albumMBId);
                } else {
                    $params[] = (new \Spieldose\Database\DBParam())->null(":album_mbid");
                }
                $trackNumber = $this->id3->getTrackNumber();
                if (! empty($trackNumber)) {
                    $params[] = (new \Spieldose\Database\DBParam())->str(":track_number", $trackNumber);
                } else {
                    $params[] = (new \Spieldose\Database\DBParam())->null(":track_number");
                }
                $discNumber = $this->id3->getDiscNumber();
                if (! empty($discNumber)) {
                    $params[] = (new \Spieldose\Database\DBParam())->str(":disc_number", $discNumber);
                } else {
                    $params[] = (new \Spieldose\Database\DBParam())->null(":disc_number");
                }
                $albumArtist = $this->id3->getAlbumArtistName();
                if (! empty($albumArtist)) {
                    $params[] = (new \Spieldose\Database\DBParam())->str(":album_artist", $albumArtist);
                } else {
                    $params[] = (new \Spieldose\Database\DBParam())->null(":album_artist");
                }
                $year = $this->id3->getYear();
                if (! empty($year)) {
                    $params[] = (new \Spieldose\Database\DBParam())->int(":year", intval($year));
                } else {
                    $params[] = (new \Spieldose\Database\DBParam())->null(":year");
                }
                $genre = $this->id3->getGenre();
                if (! empty($genre)) {
                    $params[] = (new \Spieldose\Database\DBParam())->str(":genre", $genre);
                } else {
                    $params[] = (new \Spieldose\Database\DBParam())->null(":genre");
                }
                $playtimeSeconds = $this->id3->getPlaytimeSeconds();
                if ($playtimeSeconds > 0) {
                    $params[] = (new \Spieldose\Database\DBParam())->int(":playtime_seconds", $playtimeSeconds);
                } else {
                    $params[] = (new \Spieldose\Database\DBParam())->null(":playtime_seconds");
                }
                $playtimesString = $this->id3->getPlaytimeString();
                if (! empty($playtimeSeconds)) {
                    $params[] = (new \Spieldose\Database\DBParam())->str(":playtime_string", $playtimesString);
                } else {
                    $params[] = (new \Spieldose\Database\DBParam())->null(":playtime_string");
                }
                $mimeType = $this->id3->getMimeType();
                if (! empty($mimeType)) {
                    $params[] = (new \Spieldose\Database\DBParam())->str(":mime", $mimeType);
                } else {
                    $params[] = (new \Spieldose\Database\DBParam())->null(":mime");
                }
                $this->dbh->execute('
                    REPLACE INTO FILE (
                        id,
                        local_path,
                        base_path,
                        file_name,
                        mime,
                        track_name,
                        track_artist,
                        artist_mbid,
                        album_name,
                        album_mbid,
                        album_artist,
                        disc_number,
                        track_number,
                        year,
                        genre,
                        playtime_seconds,
                        playtime_string,
                        created
                    ) VALUES (
                        :id,
                        :local_path,
                        :base_path,
                        :file_name,
                        :mime,
                        :track_name,
                        :track_artist,
                        :artist_mbid,
                        :album_name,
                        :album_mbid,
                        :album_artist,
                        :disc_number,
                        :track_number,
                        :year,
                        :genre,
                        :playtime_seconds,
                        :playtime_string,
                        strftime("%s", "now")
                    )
                ', $params);
            } else {
                throw new \Spieldose\Exception\NotFoundException("path: " . $filePath);
            }
        }

        public function getPendingArtists() {
            $artists = array();
            $query = " SELECT DISTINCT track_artist AS artist FROM FILE WHERE artist_mbid IS NULL AND track_artist IS NOT NULL ORDER BY RANDOM() ";
            $results = $this->dbh->query($query);
            $totalArtists = count($results);
            for ($i = 0; $i < $totalArtists; $i++) {
                $artists[] = $results[$i]->artist;
            }
            return($artists);
        }

        public function getPendingArtistMBIds() {
            $mbIds = array();
            $query = '
                SELECT
                    DISTINCT F.artist_mbid AS mbid
                FROM FILE F
                WHERE F.artist_mbid IS NOT NULL
                AND NOT EXISTS
                    (SELECT mbid FROM MB_CACHE_ARTIST MCA WHERE MCA.mbid = F.artist_mbid)
            ';
            $results = $this->dbh->query($query);
            $totalArtists = count($results);
            for ($i = 0; $i < $totalArtists; $i++) {
                $mbIds[] = $results[$i]->mbid;
            }
            return($mbIds);
        }

        public function mbArtistScrap($artist) {
            $mbArtist = \Spieldose\MusicBrainz\Artist::getFromArtist($artist);
            if (empty($mbArtist->mbId)) {
                $mbIds = \Spieldose\MusicBrainz\Artist::searchMusicBrainzId($artist, 1);
                if (count($mbIds) == 1) {
                    $mbArtist->mbId = $mbIds[0];
                }
            }
            if (! empty($mbArtist->mbId)) {
                $mbArtist->save($this->dbh);
                $params = array();
                $params[] = (new \Spieldose\Database\DBParam())->str(":artist_mbid", $mbArtist->mbId);
                $params[] = (new \Spieldose\Database\DBParam())->str(":track_artist", $artist);
                $this->dbh->execute(" UPDATE FILE SET artist_mbid = :artist_mbid WHERE track_artist = :track_artist ", $params);
            }
        }

        public function mbArtistMBIdscrap($mbid) {
            $mbArtist = \Spieldose\MusicBrainz\Artist::getFromMBId($mbid);
            if (! empty($mbArtist->mbId)) {
                $mbArtist->save($this->dbh);
                $params = array();
                $params[] = (new \Spieldose\Database\DBParam())->str(":old_artist_mbid", $mbid);
                $params[] = (new \Spieldose\Database\DBParam())->str(":artist_mbid", $mbArtist->mbId);
                $this->dbh->execute(" UPDATE FILE SET artist_mbid = :artist_mbid WHERE artist_mbid = :old_artist_mbid ", $params);
            }
        }

        public function getPendingAlbumMBIds() {
            $mbIds = array();
            $query = '
                SELECT
                    DISTINCT F.album_mbid AS mbid
                FROM FILE F
                WHERE NOT EXISTS
                    (SELECT mbid FROM MB_CACHE_ALBUM MCA WHERE MCA.mbid = F.album_mbid)
                AND F.album_mbid IS NOT NULL
            ';
            $results = $this->dbh->query($query);
            $totalArtists = count($results);
            for ($i = 0; $i < $totalArtists; $i++) {
                $mbIds[] = $results[$i]->mbid;
            }
            return($mbIds);
        }

        public function getPendingAlbums() {
            $query = " SELECT DISTINCT album_name AS album, COALESCE(album_artist, track_artist) AS artist FROM FILE WHERE album_mbid IS NULL AND album_name IS NOT NULL ORDER BY RANDOM() ";
            return($this->dbh->query($query));
        }

        public function mbAlbumScrap(string $album = "", string $artist = "") {
            $mbAlbum = \Spieldose\MusicBrainz\Album::getFromAlbumAndArtist($album, $artist);
            if (empty($mbArtist->mbId)) {
                $mbIds = \Spieldose\MusicBrainz\Album::searchMusicBrainzId($album, $artist, 1);
                if (count($mbIds) == 1) {
                    $mbAlbum->mbId = $mbIds[0];
                }
            }
            if (! empty($mbAlbum->mbId)) {
                $mbAlbum->save($this->dbh);
                $params = array();
                $params[] = (new \Spieldose\Database\DBParam())->str(":album_mbid", $mbAlbum->mbId);
                $params[] = (new \Spieldose\Database\DBParam())->str(":album_name", $album);
                $params[] = (new \Spieldose\Database\DBParam())->str(":track_artist", $artist);
                $params[] = (new \Spieldose\Database\DBParam())->str(":album_artist", $artist);
                $this->dbh->execute(" UPDATE FILE SET album_mbid = :album_mbid WHERE album_name = :album_name AND (track_artist = :track_artist OR album_artist = :album_artist) ", $params);
            }
        }

        public function mbAlbumMBIdScrap(string $mbid = "") {
            $mbAlbum = \Spieldose\MusicBrainz\Album::getFromMBId($mbid);
            if (! empty($mbAlbum->mbId)) {
                $mbAlbum->save($this->dbh);
                $params = array();
                $params[] = (new \Spieldose\Database\DBParam())->str(":old_album_mbid", $mbid);
                $params[] = (new \Spieldose\Database\DBParam())->str(":album_mbid", $mbAlbum->mbId);
                $this->dbh->execute("UPDATE FILE SET album_mbid = :album_mbid WHERE album_mbid = :old_album_mbid", $params);
            }
        }

        public function getAllDatabaseFiles(): array {
            $results = array();
            $query = " SELECT id, base_path AS path, file_name as filename FROM FILE ORDER BY base_path, file_name ";
            $results = $this->dbh->query($query);
            return($results);
        }

        public function removeDatabaseReferences(string $fileId) {
            $params = array(
                (new \Spieldose\Database\DBParam())->str(":file_id", $fileId)
            );
            $this->dbh->execute(" DELETE FROM STATS WHERE file_id = :file_id ", $params);
            $this->dbh->execute(" DELETE FROM PLAYLIST_TRACK WHERE file_id = :file_id ", $params);
            $this->dbh->execute(" DELETE FROM LOVED_FILE WHERE file_id = :file_id ", $params);
            $this->dbh->execute(" DELETE FROM FILE WHERE id = :file_id ", $params);
        }

    }

?>