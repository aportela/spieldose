<?php
	/*
		api method: api/track/search.php
		request method: get
		request params:
			q: string
			limit: int (optional)			
			sort: string (optional)
		response:
			success: boolean
			tracks: [ 
				{
					id: string
					mbId: string
					title: string
					{
						artist:
						{
							id: string
							mbId: string
							name: string
						}
					}
					album:
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
			require_once sprintf("%s%sconfiguration.php", dirname(dirname(dirname(__FILE__))), DIRECTORY_SEPARATOR);			
			require_once sprintf("%s%sclass.Database.php", PHP_INCLUDE_PATH, DIRECTORY_SEPARATOR);		
			require_once sprintf("%s%sclass.Track.php", PHP_INCLUDE_PATH, DIRECTORY_SEPARATOR);
			$json_response["tracks"] = Track::search(
				isset($_GET["q"]) ? $_GET["q"]: null,
				isset($_GET["limit"]) ? intval($_GET["limit"]): 32,
				isset($_GET["sort"]) ? $_GET["sort"]: null
			);
			$json_response["success"] = true; 
		}
		catch(Exception $e) {
			$json_response["success"] = false;
			$json_response["error"] = array("msg" => "artist search api fatal exception", "debug" => $e->getMessage());
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