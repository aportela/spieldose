<?php
	/*
		api method: api/user/signin.php
		request method: post
		request params:
			email: string
			password: string
		response:
			success: boolean
			errorMsg: string (optional)
			debugMsg: string (optional)
	*/
	ob_start();
	session_start();
	$json_response = array();
	if (isset($_POST["email"]) && filter_var($_POST["email"], FILTER_VALIDATE_EMAIL) && isset($_POST["password"])) {		
		require_once sprintf("%s%sconfiguration.php", dirname(dirname(dirname(__FILE__))), DIRECTORY_SEPARATOR);			
		require_once sprintf("%s%sclass.User.php", PHP_INCLUDE_PATH, DIRECTORY_SEPARATOR);
		try {
			$user = new User(null, $_POST["email"]);			
			if ($user->get()) {
				if (password_verify($_POST["password"], $user->hashed_password)) {
					$user->update_last_activity();
					$_SESSION["user_id"] = $user->id;
					$json_response["success"] = true;	
				} else {
					unset($_SESSION["user_id"]);
					$json_response["success"] = false;
					$json_response["error"] = array("msg" => "invalid password");
				}				
			} else {
				unset($_SESSION["user_id"]);
				$json_response["success"] = false;
				$json_response["error"] = array("msg" => "email not found");
			}
		} catch (Exception $e) {
			$json_response["success"] = false;
			$json_response["error"] = array("msg" => "signup api fatal exception", "debug" => $e->getMessage());
		}		
	} else {		
		unset($_SESSION["user_id"]);
		$json_response["success"] = false;
		$json_response["error"] = array("msg" => "invalid request params");
	}
	ob_clean();
	header("Content-Type: application/json; charset=utf-8");
	echo json_encode($json_response);
	ob_end_flush();
?>
