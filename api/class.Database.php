<?php
	require sprintf("%s%sdata%sconfiguration.php", dirname(dirname(__FILE__)), DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR); 
	
	class Database
	{
		private $db = null;
		
		function __construct() {
			$this->db = new PDO(sprintf("sqlite:%s", DB_PATH));
			$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		}
		
		function __destruct() {
			$this->db = null;
		}
		
		function exec($sql, $params) {			
			$sth = $this->db->prepare($sql);
			$sth->execute($params);
		}

		// TODO: don't want to get all rows but fetch method is failing so... ugly tmp hack
		function fetch($sql, $params) {
			$row = $this->fetch_all($sql, $params);
			if (count($row) > 0) {
				return($row[0]);	
			} else {
				return(null);
			}
		}

		function fetch_all($sql, $params) {	
			$sth = $this->db->prepare($sql);
			$sth->execute($params);
			return($sth->fetchAll(PDO::FETCH_CLASS));						
		}
	}
?>