<?php
	/*
		api method: api/album/get.php
		request method: get
		request params:
			id: string
		response:
			success: boolean
			metadata: {
				id: string
				name: string
				year: int
				cover: string
			}
			songs: [
				{
					id: string
					title: string
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
			if (isset($_GET["id"]) && strlen($_GET["id"]) > 0)
			{
				// configuration file	
				require_once sprintf("%s%sconfiguration.php", dirname(dirname(dirname(__FILE__))), DIRECTORY_SEPARATOR);		
				// data access layer class
				require_once sprintf("%s%sclass.Database.php", PHP_INCLUDE_PATH, DIRECTORY_SEPARATOR);		
				$db = new Database();
				$params = array(":id" => $_GET["id"]);
				$sql = " SELECT ALBUM.id AS albumId, ALBUM.name AS albumName, ARTIST.id AS artistId, ARTIST.name AS artistName, ALBUM.year, ALBUM.cover FROM ALBUM LEFT JOIN ARTIST ON ARTIST.id = ALBUM.artist_id WHERE ALBUM.id = :id ";
				$json_response["metadata"] = $db->fetch($sql, $params);
				$sql = " SELECT SONG.id, SONG.title FROM SONG LEFT JOIN ALBUM ON SONG.album_id = ALBUM.id WHERE ALBUM.id = :id ";
				$json_response["songs"] = $db->fetch_all($sql, $params);
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