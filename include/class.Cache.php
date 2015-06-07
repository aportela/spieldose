<?php
	//defined(SQLITE3_DATABASE_FULLPATH) or
		//require_once sprintf("%s%sconfiguration.php", dirname(dirname(__FILE__)), DIRECTORY_SEPARATOR); 

	class Cache {
		protected $user_id;
		protected $base_dir = null;
		
		function __construct($user_id) {
			$this->user_id = $user_id;
			$this->base_dir =  sys_get_temp_dir() . DIRECTORY_SEPARATOR . "spieldose_cache" . DIRECTORY_SEPARATOR . $user_id;
			if (! file_exists($this->base_dir)) {
				mkdir ($this->base_dir . DIRECTORY_SEPARATOR . "album", 0700, true);
				mkdir ($this->base_dir . DIRECTORY_SEPARATOR . "artist", 0700, true);
			}
		}
		
		function __destruct() {
		}
		
		public function hash($params) {
			return(sha1(serialize($params)));
		}			

				
		public function get_cache($path) {
			$path = $this->base_dir . DIRECTORY_SEPARATOR . $path;
			if (file_exists($path)) {
				$result = file_get_contents($path); 			
				if ($result) {
					return(json_decode($result));
				} else {
					return(null);
				}
			} else {
				return(null);
			}			
		}
		
		public function set_cache($path, $content) {
			$path = $this->base_dir . DIRECTORY_SEPARATOR . $path;
			if (file_exists($path)) {
				unlink($path);				
			}
			return(file_put_contents($path, json_encode($content)));
		}
	}
	
	class AlbumCache extends Cache {
		function __construct($user_id) {
			parent::__construct($user_id);
		}		
		function __destruct() {
			parent::__destruct();
		}		
		public function search($params) {
			return($this->get_cache("album" . DIRECTORY_SEPARATOR . $this->hash($params) . ".cache"));
		}		
		public function save ($params, $content) {
			$this->set_cache("album" . DIRECTORY_SEPARATOR . $this->hash($params) . ".cache", $content);
		}
	}

	class ArtistCache extends Cache {
		function __construct($user_id) {
			parent::__construct($user_id);
		}		
		function __destruct() {
			parent::__destruct();
		}		
		public function search($params) {
			return($this->get_cache("artist" . DIRECTORY_SEPARATOR . $this->hash($params) . ".cache"));
		}		
		public function save ($params, $content) {
			$this->set_cache("artist" . DIRECTORY_SEPARATOR . $this->hash($params) . ".cache", $content);
		}
	}
?>