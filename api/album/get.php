<?php
	/*
		api method: api/album/get.php
		request method: get
		request params:
			id: string (optional)
			mbId: string (optional)
		response:
			success: boolean
			metadata:
			{
				id: string
				name: string
				mbId: string
				year: int
				artist:
				{
					id: string
					name: string
					mbId: string
				}
				about: string
				cover:
				{
					small: string
					medium: string
					big: string
				}
			}
			songs:
			[
				{
					id: string
					mbId: string
					title: string
					trackNumber: int
					playtimeString: string
					artist: 
					{
						id: string
						name: string
						mbId: string
					}
				}			
			] 
			errorMsg: string (optional)
			debugMsg: string (optional)
	*/			
	ob_start();
	session_start();
	$json_response = array();
	if (isset($_SESSION["user_id"])) {	
		try {
			if ((isset($_GET["id"]) && strlen($_GET["id"]) > 0) || (isset($_GET["mbId"]) && strlen($_GET["mbId"]) > 0))
			{
				// configuration file	
				require_once sprintf("%s%sconfiguration.php", dirname(dirname(dirname(__FILE__))), DIRECTORY_SEPARATOR);		
				// data access layer class
				require_once sprintf("%s%sclass.Database.php", PHP_INCLUDE_PATH, DIRECTORY_SEPARATOR);		
				$db = new Database();
				$sql = null;
				$sql_fields = null;
				if (LASTFM_OVERWRITE) {
					$sql_fields = " ALBUM.id AS id, ALBUM.mb_id AS mbId, ALBUM.tag_name AS name, ALBUM.tag_year AS year, ALBUM.lastfm_metadata AS lastFM, ARTIST.id AS artistId, ARTIST.mb_id AS artistMbId, ARTIST.tag_name AS artistName, ARTIST.lastfm_metadata AS artist_lastfm_metadata ";
				} else {
					$sql_fields = " ALBUM.id AS id, ALBUM.mb_id AS mbId, ALBUM.tag_name AS name, ALBUM.tag_year AS year, ALBUM.lastfm_metadata AS lastFM, ARTIST.id AS artistId, ARTIST.mb_id AS artistMbId, ARTIST.tag_name AS artistName ";
				}
				$sql_where = null;
				$params = array();
				if (isset($_GET["mbId"])) {
					$sql_where = " WHERE ALBUM.mb_id = :mb_id ";
					$params = array(":mb_id" => $_GET["mbId"]);					
				} else {
					$sql_where = " WHERE ALBUM.id = :id ";
					$params = array(":id" => $_GET["id"]);
				}
				$sql = sprintf(" SELECT %s FROM ALBUM LEFT JOIN ARTIST ON ARTIST.id = ALBUM.artist_id %s ", $sql_fields, $sql_where);
				$json_response["metadata"] = $db->fetch($sql, $params);				
				$json_metadata = $json_response["metadata"]->lastFM;
				if ($json_metadata) {
					$album_metadata = json_decode($json_metadata);
					if (LASTFM_OVERWRITE) {								
						$json_response["metadata"]->name = $album_metadata->album->name;
					}							
					$json_response["metadata"]->about = $album_metadata->album->wiki->content;
					$images = array();
					foreach($album_metadata->album->image as $image) {
						$images[] = array("size" => $image->size, "url" => $image->{"#text"});
					}
					$json_response["metadata"]->cover = $images;
				}
				unset($json_response["metadata"]->lastFM);
				$json_metadata = $json_response["metadata"]->artist_lastfm_metadata;
				if ($json_metadata) {
					$artist_metadata = json_decode($json_metadata);
					$json_response["metadata"]->artist = array("id" => $json_response["metadata"]->artistId, "mbId" => $json_response["metadata"]->artistMbId, "name" => $json_response["metadata"]->artistName);
				} else {
					$json_response["metadata"]->artist = array("id" => $json_response["metadata"]->artistId, "mbId" => $json_response["metadata"]->artistMbId, "name" => $json_response["metadata"]->artistName);
				}
				unset($json_response["metadata"]->artist_lastfm_metadata);
				unset($json_response["metadata"]->artistId);
				unset($json_response["metadata"]->artistMbId);
				unset($json_response["metadata"]->artistName);
				$sql_fields = " SONG.id AS id, SONG.mb_id AS mbId, SONG.tag_track_number AS trackNumber, SONG.tag_title AS title, SONG.tag_playtime_string AS playtimeString, ARTIST.id AS artistId, ARTIST.tag_name AS artistName, ARTIST.mb_id AS artistMbId ";
				$sql = sprintf(" SELECT %s FROM SONG LEFT JOIN ALBUM ON ALBUM.id = SONG.album_id LEFT JOIN ARTIST ON ARTIST.id = SONG.artist_id %s ORDER BY SONG.tag_track_number ", $sql_fields, $sql_where);
				$json_response["songs"] = $db->fetch_all($sql, $params);
				$total_db_tracks = count($json_response["songs"]);
				if (LASTFM_OVERWRITE) {
					$total_album_tracks = count($album_metadata->album->tracks->track);										
					for ($t = 0; $t < $total_db_tracks; $t++) {
						for ($i = 0, $found = false; $i < $total_album_tracks && ! $found; $i++) {
							$mb_id = $json_response["songs"][$t]->mbId;
							$tag_title = $json_response["songs"][$t]->title;
							$lastfm_title = $album_metadata->album->tracks->track[$i]->name;
							$lastfm_mbid = $album_metadata->album->tracks->track[$i]->mbid;
							$track_number = $json_response["songs"][$t]->trackNumber;
							if ($mb_id == $lastfm_mbid) {
								$found = true;
							}
							else if ($tag_title == $lastfm_title) {
								$found = true;
							} else {
								if ($track_number == $i) {
									$found = true;
								} else {
									similar_text($tag_title, $lastfm_title, $percent);
									if ($percent > 85) {
										$found = true;
									}	
								}
							}
							if ($found) {
								$json_response["songs"][$t]->mbId = $album_metadata->album->tracks->track[$i]->mbid;
								$json_response["songs"][$t]->title = $album_metadata->album->tracks->track[$i]->name;
								$json_response["songs"][$t]->trackNumber = ($i + 1);
								$json_response["songs"][$t]->artist = array(
									"id" => $json_response["songs"][$t]->id, 
									"name" => $album_metadata->album->tracks->track[$i]->artist->name, 
									"mbId" => $album_metadata->album->tracks->track[$i]->artist->mbid
								);								
							}							
							unset($json_response["songs"][$t]->artistId); 
							unset($json_response["songs"][$t]->artistName);
							unset($json_response["songs"][$t]->artistMbId);							
						}
					}
				} else {
					for ($t = 0; $t < $total_db_tracks; $t++) {
						$json_response["songs"][$t]->artist = array(
							"id" => $json_response["songs"][$t]->artistId, 
							"name" => $json_response["songs"][$t]->artistName, 
							"mbId" => $json_response["songs"][$t]->artistMbId
						);
						unset($json_response["songs"][$t]->artistId); 
						unset($json_response["songs"][$t]->artistName);
						unset($json_response["songs"][$t]->artistMbId);
					}
				} 
				$json_response["success"] = true;
				$db = null;
			}				
		}
		catch(PDOException $e) {
			$json_response["success"] = false;
			$json_response["errorMsg"] = "artist search api fatal exception";
			$json_response["debugMsg"] = $e->getMessage();
		}
	} else {
		http_response_code(401);		
		$json_response["success"] = false;
		$json_response["errorMsg"] = "unauthorized";		
	}					
	header("Content-Type: application/json; charset=utf-8");
	echo json_encode($json_response);
	ob_end_flush();
?>