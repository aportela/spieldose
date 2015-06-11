<?php
	/*
		api method: api/track/get
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
				require_once sprintf("%s%sconfiguration.php", dirname(dirname(dirname(__FILE__))), DIRECTORY_SEPARATOR);		
				require_once sprintf("%s%sclass.Database.php", PHP_INCLUDE_PATH, DIRECTORY_SEPARATOR);		
				require_once sprintf("%s%sclass.Track.php", PHP_INCLUDE_PATH, DIRECTORY_SEPARATOR);
				$track = new Track(isset($_GET["id"]) ? $_GET["id"]: null, isset($_GET["mbId"]) ? $_GET["mbId"]: null, null);
				$path = $track->get_path(); 
				if ($path != null) {
					$track->update_song_playcount($_SESSION["user_id"]);
					Track::serve_file($path);
				} else {
					$db = null;
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