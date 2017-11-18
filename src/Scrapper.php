<?php
    declare(strict_types=1);

    namespace Spieldose;

    class Scrapper {
        private $id3 = null;

	    public function __construct () {
            $this->id3 = new \Spieldose\ID3();
        }

        public function __destruct() { }

        public function scrapFileTags(\Spieldose\Database\DB $dbh = null, string $filePath = "") {
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
                $dbh->execute('
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

        public function getPendingArtists(\Spieldose\Database\DB $dbh = null) {
            $artists = array();
            $query = "SELECT DISTINCT track_artist AS artist FROM FILE WHERE artist_mbid IS NULL AND track_artist IS NOT NULL ORDER BY track_artist";
            $results = $dbh->query($query);
            $totalArtists = count($results);
            for ($i = 0; $i < $totalArtists; $i++) {
                $artists[] = $results[$i]->artist;
            }
            return($artists);
        }

        public function getPendingArtistMBIds(\Spieldose\Database\DB $dbh = null) {
            $mbIds = array();
            $query = '
                SELECT
                    DISTINCT F.artist_mbid AS mbid
                FROM FILE F
                WHERE NOT EXISTS
                    (SELECT mbid FROM MB_CACHE_ARTIST MCA WHERE MCA.mbid = F.artist_mbid)
                AND F.artist_mbid IS NOT NULL
            ';
            $results = $dbh->query($query);
            $totalArtists = count($results);
            for ($i = 0; $i < $totalArtists; $i++) {
                $mbIds[] = $results[$i]->mbid;
            }
            return($mbIds);
        }

        public function mbArtistScrap(\Spieldose\Database\DB $dbh = null, $artist) {
            $mbArtist = \Spieldose\MusicBrainz\Artist::getFromArtist($artist);
            if (! empty($mbArtist->mbId)) {
                $mbArtist->save($dbh);
                $params = array();
                $params[] = (new \Spieldose\Database\DBParam())->str(":artist_mbid", $mbArtist->mbId);
                $params[] = (new \Spieldose\Database\DBParam())->str(":track_artist", $artist);
                $dbh->execute("UPDATE FILE SET artist_mbid = :artist_mbid WHERE track_artist = :track_artist", $params);
            }
        }

        public function mbArtistMBIdscrap(\Spieldose\Database\DB $dbh = null, $mbid) {
            $mbArtist = \Spieldose\MusicBrainz\Artist::getFromMBId($mbid);
            if (! empty($mbArtist->mbId)) {
                $mbArtist->save($dbh);
                $params = array();
                $params[] = (new \Spieldose\Database\DBParam())->str(":old_artist_mbid", $mbid);
                $params[] = (new \Spieldose\Database\DBParam())->str(":artist_mbid", $mbArtist->mbId);
                $dbh->execute("UPDATE FILE SET artist_mbid = :artist_mbid WHERE artist_mbid = :old_artist_mbid", $params);
            }
        }

        public function getPendingAlbumMBIds(\Spieldose\Database\DB $dbh = null) {
            $mbIds = array();
            $query = '
                SELECT
                    DISTINCT F.album_mbid AS mbid
                FROM FILE F
                WHERE NOT EXISTS
                    (SELECT mbid FROM MB_CACHE_ALBUM MCA WHERE MCA.mbid = F.album_mbid)
                AND F.album_mbid IS NOT NULL
            ';
            $results = $dbh->query($query);
            $totalArtists = count($results);
            for ($i = 0; $i < $totalArtists; $i++) {
                $mbIds[] = $results[$i]->mbid;
            }
            return($mbIds);
        }

        public function getPendingAlbums(\Spieldose\Database\DB $dbh = null) {
            $artists = array();
            $query = "SELECT DISTINCT album_name AS album, COALESCE(album_artist, track_artist) AS artist FROM FILE WHERE album_mbid IS NULL AND album_name IS NOT NULL ORDER BY RANDOM(), album_name";
            return($dbh->query($query));
        }

        public function mbAlbumScrap(\Spieldose\Database\DB $dbh = null, string $album = "", string $artist = "") {
            $mbAlbum = \Spieldose\MusicBrainz\Album::getFromAlbumAndArtist($album, $artist);
            if (! empty($mbAlbum->mbId)) {
                $mbAlbum->save($dbh);
                $params = array();
                $params[] = (new \Spieldose\Database\DBParam())->str(":album_mbid", $mbAlbum->mbId);
                $params[] = (new \Spieldose\Database\DBParam())->str(":album_name", $album);
                $params[] = (new \Spieldose\Database\DBParam())->str(":track_artist", $artist);
                $dbh->execute("UPDATE FILE SET album_mbid = :album_mbid WHERE album_name = :album_name AND track_artist = :track_artist", $params);
            }
        }

        public function mbAlbumMBIdScrap(\Spieldose\Database\DB $dbh = null, string $mbid = "") {
            $mbAlbum = \Spieldose\MusicBrainz\Album::getFromMBId($mbid);
            if (! empty($mbAlbum->mbId)) {
                $mbAlbum->save($dbh);
                $params = array();
                $params[] = (new \Spieldose\Database\DBParam())->str(":old_album_mbid", $mbid);
                $params[] = (new \Spieldose\Database\DBParam())->str(":album_mbid", $mbAlbum->mbId);
                $dbh->execute("UPDATE FILE SET album_mbid = :album_mbid WHERE album_mbid = :old_album_mbid", $params);
            }
        }
    }

?>