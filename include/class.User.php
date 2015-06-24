<?php
	require_once sprintf("%s%sclass.Database.php", __DIR__, DIRECTORY_SEPARATOR);
	
	class User {
		public $id;
		public $email;
		public $password;
		public $hashed_password;
		
		function __construct($id = null, $email = null) {
			$this->id = $id;
			$this->email = $email;
		}
		
		function __destruct() {
		}
		
		function get() {
			$found = false;
			try {
				$sql = null;
				$sql_fields = null;
				$sql_where = null;
				$params = array();
				if ($this->id) {
					$sql_where = " WHERE id = :id ";
					$params = array(":email" => $this->email);					
				} else if ($this->email) {
					$sql_where = " WHERE email = :email ";
					$params = array(":email" => $this->email);					
				} else {
					throw new Exception("invalid params");
				}
				if (count ($params) == 1) {
					$db = new Database();
					$sql = sprintf(" SELECT id, email, password_hash FROM USER %s ", $sql_where);
					$result = $db->fetch($sql, $params);
					if ($result) {
						$this->id = $result->id;
						$this->email = $result->email;
						$this->hashed_password = $result->password_hash;
						$found = true; 
					}
					$db = null;					
				}
				
			} catch(PDOException $e) {
				throw $e;
			}	
			return($found);									
		}
		
		function update_last_activity() {
			try {
				$db = new Database();
				$sql = " UPDATE USER SET last_activity = CURRENT_TIMESTAMP WHERE id = :id ";
				$db->exec($sql, array(":id" => $this->id));
				$db = null;
				// DATETIME(last_play, 'unixepoch') FROM PLAYED_TRACKS
			} catch(PDOException $e) {
				throw $e;
			}						
		}				
	}
?>