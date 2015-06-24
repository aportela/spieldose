<?php
	
	require_once sprintf("%s%sclass.Album.php", __DIR__, DIRECTORY_SEPARATOR);
	require_once sprintf("%s%sclass.Database.php", __DIR__, DIRECTORY_SEPARATOR);
	
	class Artist {
		public $id;
		public $mbId;
		public $name;
		public $bio;
		public $tags;
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
							//unset($album->artist);
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
		
		static function search($q = null, $genre = null, $limit = 32, $offset = 0, $sort = null) {
			try {
				$db = new Database();
				$sql = null;
				$sql_fields = null;
				if (LASTFM_OVERWRITE) {
					$sql_fields = " ARTIST.id, ARTIST.mb_id AS mbId, ARTIST.tag_name AS name, ARTIST.lastfm_metadata AS lastfm_metadata ";
				} else {
					$sql_fields = " ARTIST.id, ARTIST.mb_id AS mbId, ARTIST.tag_name AS name ";
				}
				$sql_where = array();
				$params = array();
				
				if ($q && strlen($q) > 0) {
					$sql_where[] = " ARTIST.tag_name LIKE :q ";
					$params[":q"] = '%' . $q . '%'; 
				}
				if ($genre && strlen($genre) > 0) {
					$sql_where[] = " EXISTS ( SELECT artist_id FROM ARTIST_TAG WHERE ARTIST_TAG.artist_id = ARTIST.id AND ARTIST_TAG.tag = :tag ) ";
					$params[":tag"] = $genre;
				}
				$sql_order = $sort && $sort == "rnd" ? "RANDOM()" : "ARTIST.tag_name";
				$sql_limit = sprintf(" LIMIT %d ", $limit ? $limit: 32);
				$sql_offset = sprintf(" OFFSET %d ", $offset && $offset > 0 ? $offset: 0);							
				$sql = sprintf ( " SELECT %s FROM ARTIST %s ORDER BY %s %s %s" , $sql_fields, count($sql_where) > 0 ? sprintf(" WHERE %s ", implode("AND ", $sql_where)) : "", $sql_order, $sql_limit, $sql_offset);
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
				$tags = array();
				if (isset($metadata->artist->tags) && is_object($metadata->artist->tags) && ! is_array($metadata->artist->tags->tag)) {
					$metadata->artist->tags->tag = array($metadata->artist->tags->tag);
				}				
				if (isset($metadata->artist->tags) && is_object($metadata->artist->tags)) {
					foreach($metadata->artist->tags->tag as $tag) {
						$tags[] = $tag->name;
					}										
				}
				$this->tags = $tags;
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