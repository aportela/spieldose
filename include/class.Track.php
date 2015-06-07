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
				
		static function search($q = null, $limit = 32, $sort = null) {
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
				$sql_order = $sort && $sort == "rnd" ? "RANDOM()" : "TRACK.tag_title";
				$sql_limit = $limit ? sprintf(" LIMIT %d ", $limit) : 32;							
				$sql = sprintf ( " SELECT %s FROM TRACK LEFT JOIN ARTIST ON ARTIST.id = TRACK.artist_id LEFT JOIN ALBUM ON ALBUM.id = TRACK.album_id %s ORDER BY %s %s" , $sql_fields, $sql_where, $sql_order, $sql_limit);
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
	}
?>