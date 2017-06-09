<?php

    namespace Spieldose;

    class Scrapper
    {
        private $id3 = null;
	    public function __construct () {
            $this->id3 = new \Spieldose\ID3();
        }

        public function __destruct() { }

        public function scrapFileTags(\Spieldose\Database $dbh = null, string $filePath = "") {
            if (file_exists($filePath)) {
                $this->id3->analyze($filePath);
                $params = array();
                $params[] = (new \Spieldose\DatabaseParam())->str(":id", sha1($filePath));
                $params[] = (new \Spieldose\DatabaseParam())->str(":local_path", $filePath);
                $trackName = $this->id3->getTrackTitle();
                if (! empty($trackName)) {
                    $params[] = (new \Spieldose\DatabaseParam())->str(":track_name", $trackName);
                } else {
                    $params[] = (new \Spieldose\DatabaseParam())->null(":track_name");
                }
                $trackArtist = $this->id3->getTrackArtistName();
                if (! empty($trackArtist)) {
                    $params[] = (new \Spieldose\DatabaseParam())->str(":track_artist", $trackArtist);
                } else {
                    $params[] = (new \Spieldose\DatabaseParam())->null(":track_artist");
                }
                $trackAlbum = $this->id3->getAlbum();
                if (! empty($trackAlbum)) {
                    $params[] = (new \Spieldose\DatabaseParam())->str(":album_name", $trackAlbum);
                } else {
                    $params[] = (new \Spieldose\DatabaseParam())->null(":album_name");
                }
                $trackNumber = $this->id3->getTrackNumber();
                if (! empty($trackNumber)) {
                    $params[] = (new \Spieldose\DatabaseParam())->str(":track_number", $trackNumber);
                } else {
                    $params[] = (new \Spieldose\DatabaseParam())->null(":track_number");
                }
                $discNumber = $this->id3->getDiscNumber();
                if (! empty($discNumber)) {
                    $params[] = (new \Spieldose\DatabaseParam())->str(":disc_number", $discNumber);
                } else {
                    $params[] = (new \Spieldose\DatabaseParam())->null(":disc_number");
                }
                $albumArtist = $this->id3->getAlbumArtistName();
                if (! empty($albumArtist)) {
                    $params[] = (new \Spieldose\DatabaseParam())->str(":album_artist", $albumArtist);
                } else {
                    $params[] = (new \Spieldose\DatabaseParam())->null(":album_artist");
                }
                $year = $this->id3->getYear();
                if (! empty($year)) {
                    $params[] = (new \Spieldose\DatabaseParam())->int(":year", intval($year));
                } else {
                    $params[] = (new \Spieldose\DatabaseParam())->null(":year");
                }
                $genre = $this->id3->getGenre();
                if (! empty($genre)) {
                    $params[] = (new \Spieldose\DatabaseParam())->str(":genre", $genre);
                } else {
                    $params[] = (new \Spieldose\DatabaseParam())->null(":genre");
                }
                $playtimeSeconds = $this->id3->getPlaytimeSeconds();
                if ($playtimeSeconds > 0) {
                    $params[] = (new \Spieldose\DatabaseParam())->int(":playtime_seconds", $playtimeSeconds);
                } else {
                    $params[] = (new \Spieldose\DatabaseParam())->null(":playtime_seconds");
                }
                $playtimesString = $this->id3->getPlaytimeString();
                if (! empty($playtimeSeconds)) {
                    $params[] = (new \Spieldose\DatabaseParam())->str(":playtime_string", $playtimesString);
                } else {
                    $params[] = (new \Spieldose\DatabaseParam())->null(":playtime_string");
                }

                $dbh->execute('
                    REPLACE INTO FILE (
                        id,
                        local_path,
                        track_name,
                        track_artist,
                        album_name,
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
                        :track_name,
                        :track_artist,
                        :album_name,
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

        public function getPendingArtists(\Spieldose\Database $dbh = null) {
            $artists = array();
            $query = "SELECT DISTINCT track_artist AS artist FROM FILE WHERE track_artist IS NOT NULL ORDER BY track_artist";
            $results = $dbh->query($query);
            $totalArtists = count($results);
            for ($i = 0; $i < $totalArtists; $i++) {
                $artists[] = $results[$i]->artist;
            }
            return($artists);
        }

        public function mbArtistScrap(\Spieldose\Database $dbh = null, $artist) {
            $mbArtist = \Spieldose\MusicBrainz\Artist::getFromArtist($artist);
            $mbArtist->save($dbh);
            $params = array();
            $params[] = (new \Spieldose\DatabaseParam())->str(":artist_mbid", $mbArtist->mbId);
            $params[] = (new \Spieldose\DatabaseParam())->str(":track_artist", $artist);
            $dbh->execute("UPDATE FILE SET artist_mbid = :artist_mbid WHERE track_artist = :track_artist", $params);
        }
    }

?>