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
	require sprintf("%s%sclass.Database.php", dirname(dirname(__FILE__)), DIRECTORY_SEPARATOR);	
	$json_response = array();
	if (isset($_SESSION["user_id"])) {
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
			$json_response["success"] = true;			
		} catch (Exception $e) {
			$json_response["success"] = false;
			$json_response["errorMsg"] = "signout api fatal exception";
			$json_response["debugMsg"] = $e->getMessage();
		}			
	} else {
		$json_response["success"] = false;
		$json_response["errorMsg"] = "invalid session";
	}
	ob_clean();
	echo json_encode($json_response);
	ob_end_flush();
?>