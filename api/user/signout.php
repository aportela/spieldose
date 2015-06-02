<?php
	/*
		api method: api/user/signout.php
		request method: post
		request params:
		response:
			success: boolean
			errorMsg: string (optional)
			debugMsg: string (optional)
	*/
	ob_start();
	session_start();
	$json_response = array();
	if (isset($_SESSION["user_id"])) {
		// configuration file	
		require_once sprintf("%s%sconfiguration.php", dirname(dirname(dirname(__FILE__))), DIRECTORY_SEPARATOR);
		// data access layer class
		require_once sprintf("%s%sclass.Database.php", PHP_INCLUDE_PATH, DIRECTORY_SEPARATOR);
		$db = null;		
		try {
			$db = new Database();
			$db->exec(" UPDATE USER SET last_activity = CURRENT_TIMESTAMP WHERE id = :id ", array(":id" => $_SESSION["user_id"]));
			// safe session destroy: http://stackoverflow.com/a/3948312
			$_SESSION = array();		
			if (ini_get("session.use_cookies")) {
				$params = session_get_cookie_params();
				setcookie(
					session_name(), 
					'', 
					time() - 42000,
					$params["path"], $params["domain"],
					$params["secure"], $params["httponly"]
				);
			}
			session_destroy();
			$db = null;
			$json_response["success"] = true;			
		} catch (Exception $e) {
			$json_response["success"] = false;
			$json_response["errorMsg"] = "signout api fatal exception";
			$json_response["debugMsg"] = $e->getMessage();
		} finally {
			$db = null;
		}		
	} else {
		$json_response["success"] = false;
		$json_response["errorMsg"] = "invalid session";
	}
	ob_clean();
	header("Content-Type: application/json; charset=utf-8");
	echo json_encode($json_response);
	ob_end_flush();
?>