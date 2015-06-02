<?php
	/*
		api method: api/artist/search.php
		request method: get
		request params:
			q: string
			limit: int (optional)
		response:
			success: boolean
			metadata: {
				id: string
				name: string
				genres: string
			}
			albums: [
				{
					id: string
					name: string
					year: int
					cover: string					
				}			
			] 
			errorMsg: string (optional)
			debugMsg: string (optional)
	*/			
	ob_start();
	$json_response = array();	
	try {
		if (isset($_GET["id"]) && strlen($_GET["id"]) > 0)
		{
			// configuration file	
			require_once sprintf("%s%sconfiguration.php", dirname(dirname(dirname(__FILE__))), DIRECTORY_SEPARATOR);		
			// data access layer class
			require_once sprintf("%s%sclass.Database.php", PHP_INCLUDE_PATH, DIRECTORY_SEPARATOR);		
			$db = new Database();
			$params = array(":id" => $_GET["id"]);
			$sql = " SELECT ARTIST.id, ARTIST.name, ARTIST.genres FROM ARTIST WHERE ARTIST.id = :id ";
			$json_response["metadata"] = $db->fetch_all($sql, $params);
			$sql = " SELECT ALBUM.id, ALBUM.name, ALBUM.year, ALBUM.cover FROM ALBUM WHERE ALBUM.artist_id = :id ORDER BY ALBUM.year, ALBUM.name ";
			$json_response["albums"] = $db->fetch_all($sql, $params);
			$file_db = null;
		}				
	}
	catch(PDOException $e) {
		$json_response["success"] = false;
		$json_response["errorMsg"] = "album search api fatal exception";
		$json_response["debugMsg"] = $e->getMessage();
	}					
	ob_clean();
	header("Content-Type: application/json; charset=utf-8");
	echo json_encode($json_response);
	ob_end_flush();
?>