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
		require_once sprintf("%s%sconfiguration.php", dirname(dirname(dirname(__FILE__))), DIRECTORY_SEPARATOR);			
		require_once sprintf("%s%sclass.User.php", PHP_INCLUDE_PATH, DIRECTORY_SEPARATOR);
		try {
			$user = new User($_SESSION["user_id"], null);
			$user->update_last_activity();
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
			$json_response["error"] = array("msg" => "signup api fatal exception", "debug" => $e->getMessage());
		}		
	} else {
		$json_response["success"] = false;
		$json_response["error"] = array("msg" => "invalid session");
	}
	ob_clean();
	header("Content-Type: application/json; charset=utf-8");
	echo json_encode($json_response);
	ob_end_flush();
?>