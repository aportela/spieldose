<?php
	/*
		api method: api/album/search.php
		request method: get
		request params:
			q: string
			limit: int (optional)
		response:
			success: boolean
			albums: [ 
				{
					id: string
					name: string
					artistId: string
					artistName: string
					year: int
					cover: string					
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
			// configuration file	
			require_once sprintf("%s%sconfiguration.php", dirname(dirname(dirname(__FILE__))), DIRECTORY_SEPARATOR);		
			// data access layer class
			require_once sprintf("%s%sclass.Database.php", PHP_INCLUDE_PATH, DIRECTORY_SEPARATOR);		
			$db = new Database();
			$sql = null;
			$params = array();
			$sql_limit = isset($_GET["limit"]) && is_integer(intval($_GET["limit"])) ? sprintf(" LIMIT %d ", $_GET["limit"]) : "";		 
			if (isset($_GET["q"]) && strlen($_GET["q"]) > 0) {
				$params = array(":q" => '%' . $_GET["q"] . '%');
				$sql = sprintf(" SELECT ALBUM.id, ALBUM.name, ARTIST.id AS artistId, ARTIST.name AS artistName, ALBUM.year, ALBUM.cover FROM ALBUM LEFT JOIN ARTIST ON ARTIST.id = ALBUM.artist_id WHERE ALBUM.name LIKE :q ORDER BY ALBUM.name %s ", $sql_limit);						
			} else {
				$sql = sprintf(" SELECT ALBUM.id, ALBUM.name, ARTIST.id AS artistId, ARTIST.name AS artistName, ALBUM.year, ALBUM.cover FROM ALBUM LEFT JOIN ARTIST ON ARTIST.id = ALBUM.artist_id ORDER BY ALBUM.name %s ", $sql_limit);	
			}
			$json_response["albums"] = $db->fetch_all($sql, $params);
			$db = null;
			$json_response["success"] = true; 
		}
		catch(PDOException $e) {
			$json_response["success"] = false;
			$json_response["errorMsg"] = "album search api fatal exception";
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