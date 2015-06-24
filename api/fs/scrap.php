<?php
	if (isset($_SERVER['REQUEST_METHOD'])) {
		http_response_code(405);
		die("<h1>filesystem tag scan from remote not allowed<h1>");
	} else {

		// configuration file	
		require_once sprintf("%s%sconfiguration.php", dirname(dirname(dirname(__FILE__))), DIRECTORY_SEPARATOR);
		// data access layer class
		require_once sprintf("%s%sclass.Database.php", PHP_INCLUDE_PATH, DIRECTORY_SEPARATOR);						
		// Last FM class
		require_once sprintf("%s%sclass.LastFM.php", PHP_INCLUDE_PATH, DIRECTORY_SEPARATOR);
		// Artist class
		require_once sprintf("%s%sclass.Artist.php", PHP_INCLUDE_PATH, DIRECTORY_SEPARATOR);		
		echo sprintf("[spieldose]%s", PHP_EOL);
		
		
		$db = new Database();
		
		$sql = sprintf(" SELECT ARTIST.id, ARTIST.tag_name AS name FROM ARTIST WHERE ARTIST.mb_id IS NULL OR ARTIST.lastfm_metadata IS NULL ORDER BY ARTIST.tag_name ");						
		$params = array();
		$artists = $db->fetch_all($sql, $params);
		$total_artists = count($artists);
		echo sprintf("Scrapping %d artists...%s", $total_artists, PHP_EOL);
		foreach($artists as $artist) {
			echo sprintf("Scrapping artist info for: %s... ", $artist->name);
			$json_metadata = LastFM::get_artist_info($artist->name);
			$metadata = json_decode($json_metadata);
			if (isset($metadata->error)) {
				echo sprintf("not found%s", PHP_EOL);				
			} else {
				$sql = " UPDATE ARTIST SET mb_id = :mb_id, lastfm_metadata = :lastfm_metadata WHERE id = :id ";
				$params = array("mb_id" => $metadata->artist->mbid, ":lastfm_metadata" => $json_metadata, ":id" => $artist->id);
				try {
					$db->exec($sql, $params);		
					echo sprintf("ok%s", PHP_EOL);		
				} catch(PDOException $e) {
					echo sprintf("error saving scrapped info%s\t%s%s", PHP_EOL, $e->getMessage(), PHP_EOL);
				}
				echo sprintf("\tsaving tags...");
				$a = new Artist($artist->id, $metadata->artist->mbid);
				$a->parse_lastfm($json_metadata);
				$sql = " DELETE FROM ARTIST_TAG WHERE artist_id = :artist_id ";
				$params = array(":artist_id" => $artist->id);				
				try {
					$db->exec($sql, $params);		
				} catch(PDOException $e) {
					echo sprintf("error removing old artist tags%s\t%s%s", PHP_EOL, $e->getMessage(), PHP_EOL);
				}
				foreach($a->tags as $tag) {
					$sql = " INSERT INTO ARTIST_TAG (artist_id, tag) VALUES (:artist_id, :tag) ";
					$params = array(":artist_id" => $artist->id, ":tag" => $tag);				
					try {
						$db->exec($sql, $params);		
					} catch(PDOException $e) {
						echo sprintf("error saving artist tag%s\t%s%s", PHP_EOL, $e->getMessage(), PHP_EOL);
					}					
				}	
				echo sprintf("ok%s", PHP_EOL);		
			}
		}
				
		$sql = sprintf(" SELECT ALBUM.id AS album_id, ALBUM.tag_name AS album_name, ARTIST.tag_name AS artist_name FROM ALBUM LEFT JOIN ARTIST ON ARTIST.id = ALBUM.artist_id WHERE ALBUM.mb_id IS NULL OR ALBUM.lastfm_metadata IS NULL ORDER BY ALBUM.tag_name ");						
		$params = array();
		$albums = $db->fetch_all($sql, $params);
		$total_albums = count($albums);
		echo sprintf("Scrapping %d albums...%s", $albums, PHP_EOL);
		foreach($albums as $album) {
			if ($album->artist_name) {
				echo sprintf("Scrapping album info for: %s / %s...", $album->album_name, $album->artist_name);	
			} else {
				echo sprintf("Scrapping album info for: %s...", $album->album_name);
			}			
			$json_metadata = LastFM::get_album_info($album->artist_name, $album->album_name);
			$metadata = json_decode($json_metadata);
			if (isset($metadata->error)) {
				echo sprintf("not found%s", PHP_EOL);				
			} else {
				$sql = " UPDATE ALBUM SET mb_id = :mb_id, lastfm_metadata = :lastfm_metadata WHERE id = :id ";
				$params = array("mb_id" => $metadata->album->mbid, ":lastfm_metadata" => $json_metadata, ":id" => $album->album_id);
				try {
					$db->exec($sql, $params);		
					echo sprintf("ok%s", PHP_EOL);		
				} catch(PDOException $e) {
					echo sprintf("error saving scrapped info%s\t%s%s", PHP_EOL, $e->getMessage(), PHP_EOL);
				}				
			}
		}
		
		$db = null;
	}			
?>