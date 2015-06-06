<?php
	/*
		api method: api/album/search.php
		request method: get
		request params:
			q: string
			sort: string (optional)
			limit: int (optional)			
		response:
			success: boolean
			albums:
			[ 
				{
					id: string
					name: string
					mbId: string
					year: int
					artist:
					{
						id: string
						name: string
						mbId: string
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
			// configuration file	
			require_once sprintf("%s%sconfiguration.php", dirname(dirname(dirname(__FILE__))), DIRECTORY_SEPARATOR);
			// data access layer class
			require_once sprintf("%s%sclass.Database.php", PHP_INCLUDE_PATH, DIRECTORY_SEPARATOR);		
			$db = new Database();
			$sql = null;
			$params = array();			
			$sql_fields = null;
			if (isset($_GET["q"]) && strlen($_GET["q"]) > 0) {
				if (LASTFM_OVERWRITE) {
					$sql_fields = " ALBUM.id, ALBUM.tag_name AS name, ALBUM.mb_id AS mbId, ALBUM.tag_year AS year, ALBUM.lastfm_metadata, ARTIST.id AS artistId, ARTIST.tag_name AS artistName, ARTIST.mb_id AS artistMbId, ARTIST.lastfm_metadata AS artist_lastfm_metadata ";
				} else {
					$sql_fields = " ALBUM.id, ALBUM.tag_name AS name, ALBUM.mb_id AS mbId, ALBUM.tag_year AS year, ARTIST.id AS artistId, ARTIST.tag_name AS artistName, ARTIST.mb_id AS artistMbId ";
				}
			} else {
				if (LASTFM_OVERWRITE) {
					$sql_fields = " ALBUM.id, ALBUM.tag_name AS name, ALBUM.mb_id AS mbId, ALBUM.tag_year AS year, ALBUM.lastfm_metadata, ARTIST.id AS artistId, ARTIST.tag_name AS artistName, ARTIST.mb_id AS artistMbId, ARTIST.lastfm_metadata AS artist_lastfm_metadata";
				} else {
					$sql_fields = " ALBUM.id, ALBUM.tag_name AS name, ALBUM.mb_id AS mbId, ALBUM.tag_year AS year, ARTIST.id AS artistId, ARTIST.tag_name AS artistName, ARTIST.mb_id AS artistMbId ";
				}
			}
			$sql_where = null;
			if (isset($_GET["q"]) && strlen($_GET["q"]) > 0) {
				$sql_where = " WHERE ALBUM.tag_name LIKE :q ";
			}		
			$sql_order = isset($_GET["sort"]) && $_GET["sort"] == "rnd" ? "RANDOM()" : "ALBUM.tag_name";
			$sql_limit = isset($_GET["limit"]) && is_integer(intval($_GET["limit"])) ? sprintf(" LIMIT %d ", $_GET["limit"]) : "";			
			$sql = sprintf ( " SELECT %s FROM ALBUM LEFT JOIN ARTIST ON ARTIST.id = ALBUM.artist_id %s ORDER BY %s %s" , $sql_fields, $sql_where, $sql_order, $sql_limit);
			if (isset($_GET["q"]) && strlen($_GET["q"]) > 0) {
				$params = array(":q" => '%' . $_GET["q"] . '%');
			}
			$json_response["albums"] = $db->fetch_all($sql, $params);
			$db = null;
			if (LASTFM_OVERWRITE) {
				$total = count($json_response["albums"]);
				for ($i = 0; $i < $total; $i++) {
					$json_metadata = $json_response["albums"][$i]->lastfm_metadata;
					if ($json_metadata) {
						$metadata = json_decode($json_metadata);
						$json_response["albums"][$i]->name = $metadata->album->name;
					}
					unset($json_response["albums"][$i]->lastfm_metadata);
					$json_metadata = $json_response["albums"][$i]->artist_lastfm_metadata;
					if ($json_metadata) {
						$metadata = json_decode($json_metadata);
						$json_response["albums"][$i]->artistName = $metadata->artist->name;
					}
					unset($json_response["albums"][$i]->artist_lastfm_metadata);	
					$json_response["albums"][$i]->artist = array(
						"id" => $json_response["albums"][$i]->artistId, 
						"name" => $json_response["albums"][$i]->artistName, 
						"mbId" => $json_response["albums"][$i]->artistMbId
					);
					unset($json_response["albums"][$i]->artistId);
					unset($json_response["albums"][$i]->artistName);
					unset($json_response["albums"][$i]->artistMbId);
				}
			}			
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