<?php
	/*
		api method: api/song/get
		request method: get
		request params:
			id: string
		response:
			raw bytes
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
				// common class
				require_once sprintf("%s%sclass.Common.php", PHP_INCLUDE_PATH, DIRECTORY_SEPARATOR);		
				$db = new Database();
				$params = array(":id" => $_GET["id"]);
				$sql = " SELECT path FROM SONG WHERE id = :id ";
				$result = $db->fetch($sql, $params);
				$db = null;
				if ($result) {
					Common::serve_file($result->path);
				} else {
					http_response_code(401);
					die("<h1>not found<h1>");					
				}								
			}				
		}
		catch(PDOException $e) {
			http_response_code(500);
			die("<h1>server error<h1>");
		}
	} else {
		http_response_code(401);
		die("<h1>unauthorized<h1>");		
	}					
	ob_end_flush();
?>