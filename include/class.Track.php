<?php
	require_once sprintf("%s%sclass.Artist.php", __DIR__, DIRECTORY_SEPARATOR);
	require_once sprintf("%s%sclass.Album.php", __DIR__, DIRECTORY_SEPARATOR);
	require_once sprintf("%s%sclass.Database.php", __DIR__, DIRECTORY_SEPARATOR);
	
	class Track {
		public $id;
		public $mbId;
		public $title;
		public $number;
		public $disc;
		public $playtimeString;		
		public $artist;
		public $album;
		
		function __construct($id, $mbId, $title = null, $number = null, $disc = null, $playtimeString = null, $artist = null, $album = null) {
			$this->id = $id;
			$this->mbId = $mbId;
			$this->title = $title;
			$this->number = $number;
			$this->disc = $disc;
			$this->playtimeString = $playtimeString;
			$this->artist = $artist ? $artist : new Artist(null, null, null);
			$this->album = $album ? $album : new Album(null, null, null);
		}
		
		function __destruct() {
		}
		
		function get_path() {
			$path = null;
			try {
				$db = new Database();
				$sql = null;
				$sql_fields = null;
				$sql_where = null;
				$params = array();
				if ($this->mbId) {
					$sql_where = " WHERE mb_id = :mb_id ";
					$params = array(":mb_id" => $this->mbId);					
				} else if ($this->id) {
					$sql_where = " WHERE id = :id ";
					$params = array(":id" => $this->id);
				}
				$sql = sprintf(" SELECT path FROM TRACK %s ", $sql_where);
				$result = $db->fetch($sql, $params);
				if ($result) {
					$path = $result->path;
				}
				$db = null;
				
			} catch(PDOException $e) {
				throw $e;
			}	
			return($path);									
		}
		
		function update_song_playcount($user_id) {
			try {
				$db = new Database();
				$sql = " INSERT OR REPLACE INTO PLAYED_TRACKS (user_id, track_id, play_count, last_play) VALUES (:user_id, :track_id, (IFNULL((SELECT play_count FROM PLAYED_TRACKS WHERE user_id = :user_id AND track_id = :track_id), 1) + 1), strftime('%s', 'now')) ";
				$db->exec($sql, array(":user_id" => $user_id, ":track_id" => $this->id));
				$db = null;
				// DATETIME(last_play, 'unixepoch') FROM PLAYED_TRACKS
			} catch(PDOException $e) {
				throw $e;
			}			
		}
				
		static function search($q = null, $limit = 32, $offset = 0, $sort = null) {
			try {
				$db = new Database();
				$sql = null;
				$sql_fields = null;
				if (LASTFM_OVERWRITE) {
					$sql_fields = " TRACK.id AS id, TRACK.mb_id AS mbId, TRACK.tag_title AS title, TRACK.tag_number AS number, TRACK.tag_part_of_a_set AS disc, TRACK.tag_playtime_string as playtimeString, ARTIST.id AS artistId, ARTIST.mb_id AS artistMbId, ARTIST.tag_name AS artistName, ARTIST.lastfm_metadata AS artistLastFMMetadata, ALBUM.id AS albumId, ALBUM.mb_id AS albumMbId, ALBUM.tag_name AS albumName, ALBUM.tag_year AS albumYear, ALBUM.lastfm_metadata AS albumLastFMMetadata ";
				} else {
					$sql_fields = " TRACK.id AS id, TRACK.mb_id AS mbId, TRACK.tag_title AS title, TRACK.tag_number AS number, TRACK.tag_part_of_a_set AS disc, TRACK.tag_playtime_string as playtimeString, ARTIST.id AS artistId, ARTIST.mb_id AS artistMbId, ARTIST.tag_name AS artistName, ALBUM.id AS albumId, ALBUM.mb_id AS albumMbId, ALBUM.tag_name AS albumName, ALBUM.tag_year AS albumYear ";
				}
				$sql_where = null;
				$params = array();
				if ($q && strlen($q) > 0) {
					$sql_where = " WHERE TRACK.tag_title LIKE :q ";
					$params = array(":q" => '%' . $_GET["q"] . '%');					
				}
				switch($sort) {
					case "rnd":
						$sql_order = "RANDOM()";
					break;
					case "top":
						$sql_order = "PLAYED_TRACKS.play_count DESC";
					break;
					case "recent":
						$sql_order = "TRACK.last_modified_time DESC";
					break;
					default:
						$sql_order = "TRACK.tag_title";
					break;
				}
				$sql_limit = $limit ? sprintf(" LIMIT %d ", $limit) : 32;
				if ($sort != "top") {
					$sql = sprintf ( " SELECT %s FROM TRACK LEFT JOIN ARTIST ON ARTIST.id = TRACK.artist_id LEFT JOIN ALBUM ON ALBUM.id = TRACK.album_id %s ORDER BY %s %s" , $sql_fields, $sql_where, $sql_order, $sql_limit);
				} else {
					$sql = sprintf ( " SELECT %s FROM PLAYED_TRACKS LEFT JOIN TRACK ON TRACK.id = PLAYED_TRACKS.track_id LEFT JOIN ARTIST ON ARTIST.id = TRACK.artist_id LEFT JOIN ALBUM ON ALBUM.id = TRACK.album_id %s ORDER BY %s %s" , $sql_fields, $sql_where, $sql_order, $sql_limit);				
				}
											
				$results = $db->fetch_all($sql, $params);
				$db = null;
				$tracks = array();
				$total_results = count($results);
				for ($i = 0; $i < $total_results; $i++) {
					$artist = new Artist(
						$results[$i]->artistId,
						$results[$i]->artistMbId,
						$results[$i]->artistName
					);
					if (LASTFM_OVERWRITE) {
						$artist->parse_lastfm($results[$i]->artistLastFMMetadata);
					}
					unset($artist->bio);
					unset($artist->image);
					unset($artist->albums);
					$album = new Album(
						$results[$i]->albumId,
						$results[$i]->albumMbId,
						$results[$i]->albumName,
						$results[$i]->albumYear
					);
					if (LASTFM_OVERWRITE) {
						$album->parse_lastfm($results[$i]->albumLastFMMetadata);
					}					
					unset($album->about);
					unset($album->artist);
					unset($album->tracks);
					$track = new Track(
						$results[$i]->id,
						$results[$i]->mbId,
						$results[$i]->title,
						$results[$i]->number,
						$results[$i]->disc,
						$results[$i]->playtimeString,
						$artist,
						$album
					);
					$tracks[] = $track;
				}
				return($tracks);
			} catch(PDOException $e) {
				throw $e;
			}										
		}		
		
        // serve file (with resume support)
        // kudos to Alix Axel
        // http://stackoverflow.com/a/7591130
        static function serve_file($path, $speed = null, $multipart = true) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }        
            if (is_file($path = realpath($path)) === true) {
                $file = fopen($path, 'rb');
                $size = sprintf('%u', filesize($path));
                $speed = (empty($speed) === true) ? 1024 : floatval($speed);        
                if (is_resource($file) === true) {
                    set_time_limit(0);        
                    if (strlen(session_id()) > 0) {
                        session_write_close();
                    }        
                    if ($multipart === true) {
                        $range = array(0, $size - 1);        
                        if (array_key_exists('HTTP_RANGE', $_SERVER) === true) {
                            $range = array_map('intval', explode('-', preg_replace('~.*=([^,]*).*~', '$1', $_SERVER['HTTP_RANGE'])));        
                            if (empty($range[1]) === true) {
                                $range[1] = $size - 1;
                            }                        
                            foreach ($range as $key => $value) {
                                $range[$key] = max(0, min($value, $size - 1));
                            }                        
                            if (($range[0] > 0) || ($range[1] < ($size - 1))) {
                                header(sprintf('%s %03u %s', 'HTTP/1.1', 206, 'Partial Content'), true, 206);
                            }
                        }                        
                        header('Accept-Ranges: bytes');
                        header('Content-Range: bytes ' . sprintf('%u-%u/%u', $range[0], $range[1], $size));
                    } else {
                        $range = array(0, $size - 1);
                    }                    
                    header('Pragma: public');
                    header('Cache-Control: public, no-cache');
                    header('Content-Type: application/octet-stream');
                    header('Content-Length: ' . sprintf('%u', $range[1] - $range[0] + 1));
                    header('Content-Disposition: attachment; filename="' . basename($path) . '"');
                    header('Content-Transfer-Encoding: binary');                    
                    if ($range[0] > 0) {
                        fseek($file, $range[0]);
                    }                    
                    while ((feof($file) !== true) && (connection_status() === CONNECTION_NORMAL)) {
                        echo fread($file, round($speed * 1024)); flush(); sleep(1);
                    }                    
                    fclose($file);
                }                
                exit();
            } else {
                header(sprintf('%s %03u %s', 'HTTP/1.1', 404, 'Not Found'), true, 404);
            }
            return false;
        }                        
					
	}
?>