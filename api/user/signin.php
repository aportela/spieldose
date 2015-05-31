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
	require sprintf("%s%sclass.Database.php", dirname(dirname(__FILE__)), DIRECTORY_SEPARATOR);	
	$json_response = array();
	if (isset($_POST["email"]) && filter_var($_POST["email"], FILTER_VALIDATE_EMAIL) && isset($_POST["password"])) {
		try {			
			$db = new Database();
			$user = $db->fetch(" SELECT id, password_hash FROM USER WHERE email = :email ", array(":email" => $_POST["email"]));
			if ($user) {
				if (password_verify($_POST["password"], $user->password_hash)) {
					$_SESSION["user_id"] = $user->id;
					$db->exec(" UPDATE USER SET last_activity = CURRENT_TIMESTAMP WHERE id = :id ", array(":id" => $_SESSION["user_id"]));
					$json_response["success"] = true;	
				} else {
					unset($_SESSION["user_id"]);
					$json_response["success"] = false;
					$json_response["errorMsg"] = "invalid password";
				}					
			} else {
					unset($_SESSION["user_id"]);
					$json_response["success"] = false;
					$json_response["errorMsg"] = "email not found";			
			}
		} catch (Exception $e) {
			$json_response["success"] = false;
			$json_response["errorMsg"] = "signup api fatal exception";
			$json_response["debugMsg"] = $e->getMessage();
		}		
	} else {		
		unset($_SESSION["user_id"]);
		$json_response["success"] = false;
		$json_response["errorMsg"] = "invalid request params";		
	}
	ob_clean();
	echo json_encode($json_response);
	ob_end_flush();
?>
