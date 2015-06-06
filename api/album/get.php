<?php
	/*
		api method: api/album/get.php
		request method: get
		request params:
			id: string (optional)
			mbId: string (optional)
		response:
			success: boolean
			album:
			{
				id: string
				mbId: string
				name: string
				year: int
				artist:
				{
					id: string
					mbId: string
					name: string
				}
				about: string
				image:
				{
					small: string
					medium: string
					large: string
					extralarge: string
					mega: string
				}
				songs:
				[
					{
						id: string
						mbId: string
						title: string
						number: int
						playtimeString: string
						artist: 
						{
							id: string
							mbId: string
							name: string
						}
					}			
				] 
			}
			error: (optional)
			{
				msg: string
				debug: string
			}
	*/			
	ob_start();
	session_start();
	$json_response = array();
	if (isset($_SESSION["user_id"])) {	
		try {
			if ((isset($_GET["id"]) && strlen($_GET["id"]) > 0) || (isset($_GET["mbId"]) && strlen($_GET["mbId"]) > 0)) {
				require_once sprintf("%s%sconfiguration.php", dirname(dirname(dirname(__FILE__))), DIRECTORY_SEPARATOR);		
				require_once sprintf("%s%sclass.Database.php", PHP_INCLUDE_PATH, DIRECTORY_SEPARATOR);		
				require_once sprintf("%s%sclass.Album.php", PHP_INCLUDE_PATH, DIRECTORY_SEPARATOR);
				$album = new Album(isset($_GET["id"]) ? $_GET["id"]: null, isset($_GET["mbId"]) ? $_GET["mbId"]: null, null);
				if ($album->get()) {
					$json_response["album"] = $album;
					$json_response["success"] = true;					
				} else {
					$json_response["success"] = false;
					$json_response["error"] = array("msg" => "album not found");
				}
			}				
		}
		catch(Exception $e) {
			$json_response["success"] = false;
			$json_response["error"] = array("msg" => "album metadata api fatal exception", "debug" => $e->getMessage());
		}
	} else {
		http_response_code(401);
		$json_response["success"] = false;
		$json_response["error"] = array("msg" => "unauthorized");		
	}					
	header("Content-Type: application/json; charset=utf-8");
	echo json_encode($json_response);
	ob_end_flush();
?>