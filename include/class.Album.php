<?php	
	require_once sprintf("%s%sclass.Artist.php", __DIR__, DIRECTORY_SEPARATOR);
	require_once sprintf("%s%sclass.Track.php", __DIR__, DIRECTORY_SEPARATOR);
	require_once sprintf("%s%sclass.Database.php", __DIR__, DIRECTORY_SEPARATOR);
	
	class Album {
		public $id;
		public $mbId;
		public $name;
		public $year;
		public $image;
		public $about;
		public $artist;
		public $tracks;

		function __construct($id, $mbId, $name = null, $year = null, $image = null, $about = null, $artist = null, $tracks = null) {
			$this->id = $id;
			$this->mbId = $mbId;
			$this->name = $name;
			$this->year = $year;			
			$this->image = $image && count($image) > 0 ? $image : array();
			$this->about = $about;
			$this->artist = $artist ? $artist : new Artist(null, null);
			$this->tracks = $tracks && count($tracks) > 0 ? $tracks: array();
		}
		
		function __destruct() {
		}
		
		function get() {
			try {
				$db = new Database();
				$sql = null;
				$sql_fields = null;
				if (LASTFM_OVERWRITE) {
					$sql_fields = " ALBUM.id AS id, ALBUM.mb_id AS mbId, ALBUM.tag_name AS name, ALBUM.tag_year AS year, ALBUM.lastfm_metadata AS lastfm_metadata, ARTIST.id AS artistId, ARTIST.mb_id AS artistMbId, ARTIST.tag_name AS artistName ";
				} else {
					$sql_fields = " ALBUM.id AS id, ALBUM.mb_id AS mbId, ALBUM.tag_name AS name, ALBUM.tag_year AS year, ARTIST.id AS artistId, ARTIST.mb_id AS artistMbId, ARTIST.tag_name AS artistName ";
				}
				$sql_where = null;
				$params = array();
				if ($this->mbId) {
					$sql_where = " WHERE ALBUM.mb_id = :mb_id ";
					$params = array(":mb_id" => $this->mbId);					
				} else if ($this->id) {
					$sql_where = " WHERE ALBUM.id = :id ";
					$params = array(":id" => $this->id);
				}
				$sql = sprintf(" SELECT %s FROM ALBUM LEFT JOIN ARTIST ON ARTIST.id = ALBUM.artist_id %s ", $sql_fields, $sql_where);
				$result = $db->fetch($sql, $params);
				if ($result) {
					$this->id = $result->id;
					$this->mbId = $result->mbId;
					$this->name = $result->name;
					$this->year = $result->year;				
					$this->artist = new Artist(
						$result->artistId,
						$result->artistMbId,
						$result->artistName
					);
					$this->image = array();
					$sql_fields = " TRACK.id AS id, TRACK.mb_id AS mbId, TRACK.tag_number AS number, TRACK.tag_title AS title, TRACK.tag_playtime_string AS playtimeString, ARTIST.id AS artistId, ARTIST.tag_name AS artistName, ARTIST.mb_id AS artistMbId ";
					$sql = sprintf(" SELECT %s FROM TRACK LEFT JOIN ALBUM ON ALBUM.id = TRACK.album_id LEFT JOIN ARTIST ON ARTIST.id = TRACK.artist_id %s ORDER BY TRACK.tag_number ", $sql_fields, $sql_where);
					$results = $db->fetch_all($sql, $params);
					$total_db_tracks = count($results);
					if ($total_db_tracks > 0) {
						$tracks = array();					
						for ($i = 0; $i < $total_db_tracks; $i++) {
							$artist = new Artist(
									$results[$i]->artistId,
									$results[$i]->artistMbId,
									$results[$i]->artistName
							);
							$track = new Track(
								$results[$i]->id,
								$results[$i]->mbId,
								$results[$i]->title,
								$results[$i]->number,
								null,
								$results[$i]->playtimeString,
								$artist,
								null							
							);
							unset($track->album);
							$tracks[] = $track;
						}
						$this->tracks = $tracks;						
					} else {
						$this->tracks = array();
					}
					if (LASTFM_OVERWRITE) {
						$this->parse_lastfm($result->lastfm_metadata);
					}					
					$db = null;
					return(true);					
				} else {
					$db = null;
					return(false);
				}
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
					$sql_fields = " ALBUM.id AS id, ALBUM.mb_id AS mbId, ALBUM.tag_name AS name, ALBUM.tag_year AS year, ALBUM.lastfm_metadata AS lastfm_metadata, ARTIST.id AS artistId, ARTIST.mb_id AS artistMbId, ARTIST.tag_name AS artistName ";
				} else {
					$sql_fields = " ALBUM.id AS id, ALBUM.mb_id AS mbId, ALBUM.tag_name AS name, ALBUM.tag_year AS year, ARTIST.id AS artistId, ARTIST.mb_id AS artistMbId, ARTIST.tag_name AS artistName ";
				}
				$sql_where = null;
				$params = array();
				if ($q && strlen($q) > 0) {
					$sql_where = " WHERE ALBUM.tag_name LIKE :q ";
					$params = array(":q" => '%' . $_GET["q"] . '%');					
				}
				$sql_order = $sort && $sort == "rnd" ? "RANDOM()" : "ALBUM.tag_name";
				$sql_limit = sprintf(" LIMIT %d ", $limit ? $limit: 32);
				$sql_offset = sprintf(" OFFSET %d ", $offset && $offset > 0 ? $offset: 0);											
				$sql = sprintf ( " SELECT %s FROM ALBUM LEFT JOIN ARTIST ON ARTIST.id = ALBUM.artist_id %s ORDER BY %s %s %s " , $sql_fields, $sql_where, $sql_order, $sql_limit, $sql_offset);
				$results = $db->fetch_all($sql, $params);
				$db = null;
				$albums = array();
				$total_results = count($results);
				for ($i = 0; $i < $total_results; $i++) {
					$artist = new Artist(
						$results[$i]->artistId,
						$results[$i]->artistMbId,
						$results[$i]->artistName
					);
					unset($artist->bio);
					unset($artist->image);
					unset($artist->albums);
					$album = new Album(
						$results[$i]->id,
						$results[$i]->mbId,
						$results[$i]->name,
						$results[$i]->year,
						null,
						null,
						$artist
					);
					if (LASTFM_OVERWRITE) {
						$album->parse_lastfm($results[$i]->lastfm_metadata);
					}					
					unset($album->tracks);
					unset($album->about);
					$albums[] = $album;
				}
				return($albums);
			} catch(PDOException $e) {
				throw $e;
			}										
		}
	
		function parse_lastfm($lastfm_json) {
			$metadata = json_decode($lastfm_json);
			if ($metadata) {
				$this->mbId = $metadata->album->mbid;
				$this->name = $metadata->album->name;
				$images = array();
				foreach($metadata->album->image as $image) {
					$url = $image->{"#text"};
					if ($url && strlen($url) > 0) 
					{
						$images[] = array("size" => $image->size, "url" => $image->{"#text"});						
					}					
				}
				$this->image = $images;
				$this->about = isset($metadata->album->wiki->content) ? $metadata->album->wiki->content : null;
				$this->artist = array(
					"id" => $this->artist->id,
					"mbId" => $this->artist->mbId,
					"name" => $metadata->album->artist
				);
				if (isset($this->tracks)) {
					$total_db_tracks = count($this->tracks);
					$total_lastfm_tracks = isset($metadata->album->tracks->track) ? count($metadata->album->tracks->track) : 0;
					$tracks = array();
					$orphan_tracks = array();
					if (is_object($metadata->album->tracks) && ! is_array($metadata->album->tracks->track)) {
						$metadata->album->tracks->track = array($metadata->album->tracks->track);
					}
					for ($i = 0; $i < $total_db_tracks; $i++) {					
						$match_track = null;
						$match_track_idx = null;
						for ($t = 0, $found = false; $t < $total_lastfm_tracks && ! $found; $t++) {						
							$tag_title = mb_strtolower($this->tracks[$i]->title);
							$lastfm_title = mb_strtolower($metadata->album->tracks->track[$t]->name); 
							if (
								strlen($this->tracks[$i]->mbId) > 0 && $this->tracks[$i]->mbId == $metadata->album->tracks->track[$t]->mbid || $tag_title == $lastfm_title								
							) {
								$match_track = $metadata->album->tracks->track[$t];
								$match_track_idx = $t;
							} else if ($this->tracks[$i]->number == $t + 1) {
								similar_text($tag_title, $lastfm_title, $percent);
								if ($percent > 40) {
									$match_track = $metadata->album->tracks->track[$t];
									$match_track_idx = $t;
								} 
							} else {
								similar_text($tag_title, $lastfm_title, $percent);
								if ($percent > 90) {
									$match_track = $metadata->album->tracks->track[$t];
									$match_track_idx = $t;
								}
							} 
						}
						if ($match_track != null) {
							$track = new Track(
								$this->tracks[$i]->id,
								$match_track->mbid,
								$match_track->name,
								$match_track_idx + 1,
								null,
								$this->tracks[$i]->playtimeString,
								new Artist(
									$this->tracks[$i]->artist->id,
									$match_track->artist->mbid,
									$match_track->artist->name
								),
								new Album(null, null, null)
							);
							unset($track->artist->bio);
							unset($track->artist->image);
							unset($track->artist->albums);
							unset($track->album);
							$tracks[] = $track;	
						} else {
							$track = new Track(
								$this->tracks[$i]->id,
								$this->tracks[$i]->mbId,
								$this->tracks[$i]->title,
								$this->tracks[$i]->number,
								null,
								$this->tracks[$i]->playtimeString,
								new Artist(
									$this->tracks[$i]->artist->id,
									$this->tracks[$i]->artist->mbId,
									$this->tracks[$i]->artist->name
								),
								new Album(null, null, null)
							);											
							unset($track->artist->bio);
							unset($track->artist->image);
							unset($track->artist->albums);
							unset($track->album);
							$orphan_tracks[] = $track;
						}				
					}
					$this->tracks = $tracks;
					if (count($orphan_tracks) > 0) {
						foreach($orphan_tracks as $track) {
							$this->tracks[] = $track;
						}	
					}
				}				
			}
		}				
	}	
?>