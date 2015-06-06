<?php
	require_once sprintf("%s%sclass.Artist.php", __DIR__, DIRECTORY_SEPARATOR);
	require_once sprintf("%s%sclass.Album.php", __DIR__, DIRECTORY_SEPARATOR);
	require_once sprintf("%s%sclass.Database.php", __DIR__, DIRECTORY_SEPARATOR);
	
	class Track {
		public $id;
		public $mbId;
		public $number;
		public $title;
		public $playtimeString;		
		public $artist;
		public $album;
		
		function __construct($id, $mbId, $number, $title = null, $playtimeString, $artist = null, $album = null) {
			$this->id = $id;
			$this->mbId = $mbId;
			$this->number = $number;
			$this->title = $title;
			$this->playtimeString = $playtimeString;
			$this->artist = $artist ? $artist : new Artist(null, null, null);
			$this->album = $album ? $album : new Album(null, null, null);
		}
		
		function __destruct() {
		}
				
	}
?>