<?php
	/*
		api method: api/artist/get.php
		request method: get
		request params:
			id: string (optional)
			mbId: string (optional)
		response:
			success: boolean
			artist:
			{
				id: string
				mbId: string
				name: string
				bio: string
				image:
				{
					small: string
					medium: string
					large: string
					extralarge: string
					mega: string
				}
				albums:
				[
					{
						id: string
						mbId: string
						name: string
						year: int
						image:
						{
							small: string
							medium: string
							large: string
							extralarge: string
							mega: string
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
				require_once sprintf("%s%sclass.Artist.php", PHP_INCLUDE_PATH, DIRECTORY_SEPARATOR);
				$artist = new Artist(isset($_GET["id"]) ? $_GET["id"]: null, isset($_GET["mbId"]) ? $_GET["mbId"]: null, null);
				if ($artist->get()) {
					$json_response["artist"] = $artist;
					$json_response["success"] = true;					
				} else {
					$json_response["success"] = false;
					$json_response["error"] = array("msg" => "artist not found");
				}
			}				
		}
		catch(Exception $e) {
			$json_response["success"] = false;
			$json_response["error"] = array("msg" => "artist metadata api fatal exception", "debug" => $e->getMessage());
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