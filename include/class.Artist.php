<?php
	
	require_once sprintf("%s%sclass.Album.php", __DIR__, DIRECTORY_SEPARATOR);
	require_once sprintf("%s%sclass.Database.php", __DIR__, DIRECTORY_SEPARATOR);
	
	class Artist {
		public $id;
		public $mbId;
		public $name;
		public $bio;
		public $image;
		public $albums;

		function __construct($id, $mbId, $name = null, $bio = null, $image = null, $albums = null) {
			$this->id = $id;
			$this->mbId = $mbId;
			$this->name = $name;
			$this->bio = $bio;
			$this->image = (count($image) > 0) ? $image : array();
			$this->albums = (count($albums) > 0) ? $albums : array();				
		}
		
		function __destruct() {
		}
		
		function get() {
			try {
				$db = new Database();
				$sql = null;
				$sql_fields = null;
				if (LASTFM_OVERWRITE) {
					$sql_fields = " ARTIST.id, ARTIST.mb_id AS mbId, ARTIST.tag_name AS name, ARTIST.lastfm_metadata AS lastfm_metadata";
				} else {
					$sql_fields = " ARTIST.id, ARTIST.mb_id AS mbId, ARTIST.tag_name AS name ";
				}
				$sql_where = null;
				$params = array();
				if ($this->mbId) {
					$sql_where = " WHERE ARTIST.mb_id = :mb_id ";
					$params = array(":mb_id" => $_GET["mbId"]);					
				} else if ($this->id) {
					$sql_where = " WHERE ARTIST.id = :id ";
					$params = array(":id" => $_GET["id"]);
				}
				$sql = sprintf(" SELECT %s FROM ARTIST %s ", $sql_fields, $sql_where);
				$result = $db->fetch($sql, $params);
				if ($result) {
					
					$this->id = $result->id;
					$this->mbId = $result->mbId;
					$this->name = $result->name;
					$this->bio = null;
					$this->image = array();					
					$sql_fields = " ALBUM.id AS id, ALBUM.mb_id AS mbId, ALBUM.tag_name AS name, ALBUM.tag_year AS year, ALBUM.lastfm_metadata AS lastfm_metadata ";
					$sql = sprintf(" SELECT %s FROM ALBUM INNER JOIN ARTIST ON ARTIST.id = ALBUM.artist_id %s ORDER BY ALBUM.tag_year, ALBUM.tag_name ", $sql_fields, $sql_where);
					$results = $db->fetch_all($sql, $params);
					$total_db_albums = count($results);
					if ($total_db_albums > 0) {
						$albums = array();	
						for ($i = 0; $i < $total_db_albums; $i++) {
							$album = new Album(
								$results[$i]->id,
								$results[$i]->mbId,
								$results[$i]->name,
								$results[$i]->year
							);
							if (LASTFM_OVERWRITE) {
								$album->parse_lastfm($results[$i]->lastfm_metadata);								
							}
							unset($album->artist);
							unset($album->tracks);
							unset($album->about);
							$albums[] = $album;
						}
						$this->albums = $albums;
					} else {
						$this->albums = array();
					}
					if (LASTFM_OVERWRITE) {
						$this->parse_lastfm($result->lastfm_metadata);
					}
					return(true);					
				} else {
					return(false);
				}
				$db = null;
			} catch(PDOException $e) {
				throw $e;
			}							
		}
		
		static function search($q = null, $limit = 32, $sort = null) {
			try {
				$db = new Database();
				$sql = null;
				$sql_fields = null;
				if (LASTFM_OVERWRITE) {
					$sql_fields = " ARTIST.id, ARTIST.mb_id AS mbId, ARTIST.tag_name AS name, ARTIST.lastfm_metadata AS lastfm_metadata ";
				} else {
					$sql_fields = " ARTIST.id, ARTIST.mb_id AS mbId, ARTIST.tag_name AS name ";
				}
				$sql_where = null;
				$params = array();
				
				if ($q && strlen($q) > 0) {
					$sql_where = " WHERE ARTIST.tag_name LIKE :q ";
					$params = array(":q" => '%' . $_GET["q"] . '%');										
				}
				$sql_order = $sort && $sort == "rnd" ? "RANDOM()" : "ARTIST.tag_name";
				$sql_limit = $limit ? sprintf(" LIMIT %d ", $limit) : 32;							
				$sql = sprintf ( " SELECT %s FROM ARTIST %s ORDER BY %s %s" , $sql_fields, $sql_where, $sql_order, $sql_limit);
				$results = $db->fetch_all($sql, $params);
				$db = null;
				$artists = array();
				$total_results = count($results);
				for ($i = 0; $i < $total_results; $i++) {
					if (LASTFM_OVERWRITE) {
						$artist = new Artist(
							$results[$i]->id,
							$results[$i]->mbId,
							$results[$i]->name
						);
						$artist->parse_lastfm($results[$i]->lastfm_metadata);
					} else {
						$artist = new Artist(
							$results[$i]->id,
							$results[$i]->mbId,
							$results[$i]->name
						);
					}
					unset($artist->bio);
					unset($artist->albums);						
					$artists[] = $artist;
				}
				return($artists);
			} catch(PDOException $e) {
				throw $e;
			}										
		}
				
		function parse_lastfm($lastfm_json) {
			$metadata = json_decode($lastfm_json);
			if ($metadata) {
				$this->mbId = $metadata->artist->mbid;
				$this->name = $metadata->artist->name;
				$this->bio = $metadata->artist->bio->content;
				$images = array();
				foreach($metadata->artist->image as $image) {
					$url = $image->{"#text"};
					if ($url && strlen($url) > 0) 
					{
						$images[] = array("size" => $image->size, "url" => $image->{"#text"});	
					}
				}
				$this->image = $images;				
			}
		}						
	}	
?>