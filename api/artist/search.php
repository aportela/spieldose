<?php
	/*
		api method: api/artist/search.php
		request method: get
		request params:
			q: string
			sort: string (optional)
			limit: int (optional)			
		response:
			success: boolean
			artists:
			[ 
				{
					id: string
					name: string
					mbId: string
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
			$sql_fields = null;
			$sql_where = null;
			if (LASTFM_OVERWRITE) {
				$sql_fields = " ARTIST.id, ARTIST.mb_id AS mbId, ARTIST.tag_name AS name, ARTIST.lastfm_metadata ";
			} else {
				$sql_fields = " ARTIST.id, ARTIST.mb_id AS mbId, ARTIST.tag_name AS name ";
			}	
			if (isset($_GET["q"]) && strlen($_GET["q"]) > 0) {												
				$sql_where = " WHERE ARTIST.tag_name LIKE :q ";
				$params = array(":q" => '%' . $_GET["q"] . '%');										
			}
			$sql_order = isset($_GET["sort"]) && $_GET["sort"] == "rnd" ? "RANDOM()" : "ARTIST.tag_name";
			$sql_limit = isset($_GET["limit"]) && is_integer(intval($_GET["limit"])) ? sprintf(" LIMIT %d ", $_GET["limit"]) : "";			
			$sql = sprintf ( " SELECT %s FROM ARTIST %s ORDER BY %s %s" , $sql_fields, $sql_where, $sql_order, $sql_limit);
			$json_response["artists"] = $db->fetch_all($sql, $params);
			$db = null;
			if (LASTFM_OVERWRITE) {
				$total = count($json_response["artists"]);
				for ($i = 0; $i < $total; $i++) {
					$json_metadata = $json_response["artists"][$i]->lastfm_metadata;
					if ($json_metadata) {
						$metadata = json_decode($json_metadata);
						$json_response["artists"][$i]->mbId = $metadata->artist->mbid;
						$json_response["artists"][$i]->name = $metadata->artist->name;
					}
					unset($json_response["artists"][$i]->lastfm_metadata);
				}
			}			
			$json_response["success"] = true; 
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