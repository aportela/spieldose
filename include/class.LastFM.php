<?php
	// API keys
	define("LAST_FM_API_KEY", "40ede2a05c97a8a8055ee12f813a417d");

	class LastFM {	
			
		// Get the metadata for an artist. Includes biography, truncated at 300 characters. 
		// http://www.lastfm.es/api/show/artist.getInfo
		public static function get_artist_info($artist_name, $mb_id = null) {
			if ($mb_id) {
				return(file_get_contents('http://ws.audioscrobbler.com/2.0/?method=artist.getinfo&format=json&api_key=' . LAST_FM_API_KEY . '&autocorrect=1&mbid=' . urlencode($mb_id)));
			} else {
				return(file_get_contents('http://ws.audioscrobbler.com/2.0/?method=artist.getinfo&format=json&api_key=' . LAST_FM_API_KEY . '&autocorrect=1&artist=' . urlencode($artist_name)));				
			}
		}		
		
		// Get the metadata and tracklist for an album on Last.fm using the album name or a musicbrainz id.  
		// http://www.lastfm.es/api/show/album.getInfo
		public static function get_album_info($artist_name, $album_name, $mb_id = null) {
			if ($mb_id) {
				return(file_get_contents('http://ws.audioscrobbler.com/2.0/?method=album.getinfo&format=json&api_key=' . LAST_FM_API_KEY . '&autocorrect=1&mbid=' . urlencode($mb_id)));
			} else {
				return(file_get_contents('http://ws.audioscrobbler.com/2.0/?method=album.getinfo&format=json&api_key=' . LAST_FM_API_KEY . '&autocorrect=1&artist=' . urlencode($artist_name) . '&album=' . urlencode($album_name)));				
			}			
		}		
	}
?>