<?php
	/*
		api method: api/album/search.php
		request method: get
		request params:
			q: string
			limit: int (optional)
			offset: int (optional)			
			sort: string (optional)
		response:
			success: boolean
			albums:
			[
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
			require_once sprintf("%s%sconfiguration.php", dirname(dirname(dirname(__FILE__))), DIRECTORY_SEPARATOR);			
			require_once sprintf("%s%sclass.Database.php", PHP_INCLUDE_PATH, DIRECTORY_SEPARATOR);		
			require_once sprintf("%s%sclass.Cache.php", PHP_INCLUDE_PATH, DIRECTORY_SEPARATOR);
			require_once sprintf("%s%sclass.Album.php", PHP_INCLUDE_PATH, DIRECTORY_SEPARATOR);
			$album_cache = new AlbumCache($_SESSION["user_id"]);
			$params = array(
				isset($_GET["q"]) ? $_GET["q"]: null,
				isset($_GET["limit"]) ? intval($_GET["limit"]): 32,
				isset($_GET["offset"]) ? intval($_GET["offset"]): 0,
				isset($_GET["sort"]) ? $_GET["sort"]: null
			);
			$cache = USE_CACHE ? $album_cache->search($params) : null;
			if ($cache) {
				$json_response["albums"] = $cache;
			} else {
				$json_response["albums"] = Album::search(
					isset($_GET["q"]) ? $_GET["q"]: null,
					isset($_GET["limit"]) ? intval($_GET["limit"]): 32,
					isset($_GET["offset"]) ? intval($_GET["offset"]): 0,
					isset($_GET["sort"]) ? $_GET["sort"]: null
				);
				if (USE_CACHE) {
					$album_cache->save($params, $json_response["albums"]);
				}
			}		
			$json_response["success"] = true; 
		}
		catch(Exception $e) {
			$json_response["success"] = false;
			$json_response["error"] = array("msg" => "album search api fatal exception", "debug" => $e->getMessage());
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