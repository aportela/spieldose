<?php
	require_once sprintf("%s%sclass.Database.php", __DIR__, DIRECTORY_SEPARATOR);
	
	class Tag {
		public $name;
		
		function __construct($name = null) {
			$this->name = $name;
		}
		
		function __destruct() {
		}
						
		static function search($q = null, $sort = null) {
			try {
				$db = new Database();
				$sql = null;
				$sql_where = null;
				$params = array();
				if ($q && strlen($q) > 0) {
					$sql_where = " WHERE name LIKE :q ";
					$params = array(":q" => '%' . $_GET["q"] . '%');					
				}
				switch($sort) {
					case "rnd":
						$sql_order = "RANDOM()";
					break;
					case "top":
						$sql_order = "COUNT(artist_id) DESC";
					break;
					default:
						$sql_order = "tag";
					break;
				}
				if ($sort != "top") {
					$sql = sprintf ( " SELECT DISTINCT tag AS name FROM ARTIST_TAG %s ORDER BY %s" , $sql_where, $sql_order);
				} else {
					$sql = sprintf ( " SELECT DISTINCT tag AS name FROM ARTIST_TAG %s GROUP BY tag ORDER BY %s" , $sql_where, $sql_order);			
				}
				$tags = $db->fetch_all($sql, $params);
				$db = null;
				return($tags);
			} catch(PDOException $e) {
				throw $e;
			}										
		}									
	}
?>