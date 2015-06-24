<?php
	/*
		api method: api/tag/search.php
		request method: get
		request params:
			q: string
			sort: string (optional)
		response:
			success: boolean
			tags:
			[ 
				{
					name: string
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
			require_once sprintf("%s%sclass.Tag.php", PHP_INCLUDE_PATH, DIRECTORY_SEPARATOR);
			$tag_cache = new TagCache($_SESSION["user_id"]);
			$params = array(
				isset($_GET["q"]) ? $_GET["q"]: null,				
				isset($_GET["sort"]) ? $_GET["sort"]: null
			); 
			$cache = USE_CACHE ? $tag_cache->search($params) : null;
			if ($cache) {
				$json_response["tags"] = $cache;
			} else {				
				$json_response["tags"] = Tag::search(
					isset($_GET["q"]) ? $_GET["q"]: null,					
					isset($_GET["sort"]) ? $_GET["sort"]: null
				);			
				if (USE_CACHE) {	
					$tag_cache->save($params, $json_response["tags"]);
				}
			}
			$json_response["success"] = true; 
		}
		catch(Exception $e) {
			$json_response["success"] = false;
			$json_response["error"] = array("msg" => "tag search api fatal exception", "debug" => $e->getMessage());
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