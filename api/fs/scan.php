<?php
	if (isset($_SERVER['REQUEST_METHOD'])) {
		http_response_code(405);
		die("<h1>filesystem tag scan from remote not allowed<h1>");
	} else {

		// convert $str to UTF-8 string (if required)		
		function convert_to_utf8($str) {
			if ($str != null && strlen($str) > 0) {
				if (mb_detect_encoding($str, 'UTF-8', true)) {
					return($str);
				} else {
					return(utf8_encode($str));
				}		
			} else {
				return(null);
			}
		}				
		
		function get_tag_field_value($id3_obj, $tag_field) {	
			if (isset($id3_obj['tags_html']['id3v2'][$tag_field][0])) {
				return(html_entity_decode($id3_obj['tags_html']['id3v2'][$tag_field][0]));
			} else if (isset($id3_obj['tags_html']['id3v2'][$tag_field][0])) {
				return(html_entity_decode($id3_obj['tags_html']['id3v1'][$tag_field][0]));
			} else if (isset($id3_obj['tags_html']['vorbiscomment'])) {
				if (isset($id3_obj['tags_html']['vorbiscomment'][$tag_field][0])) {
					return (html_entity_decode($id3_obj['tags_html']['vorbiscomment'][$tag_field][0]));
				}
				else {
					// try to guess not matched tags
					$tag = null;
					switch($tag_field) {
						case 'track_number':
							if (isset($id3_obj['tags_html']['vorbiscomment']['tracknumber'][0])) {
								$tag = $id3_obj['tags_html']['vorbiscomment']['tracknumber'][0];
							}						
						break;
						case 'year':
							if (isset($id3_obj['tags_html']['vorbiscomment']['date'][0])) {
								$tag = $id3_obj['tags_html']['vorbiscomment']['date'][0];
							}
						break;
						default:
							$tag = null;
						break;
					}
					return($tag);
				}
			} else {
				return(null);
			}
		}		
		
		
		// configuration file	
		require_once sprintf("%s%sconfiguration.php", dirname(dirname(dirname(__FILE__))), DIRECTORY_SEPARATOR);		
		// data access layer class
		require_once sprintf("%s%sclass.Database.php", PHP_INCLUDE_PATH, DIRECTORY_SEPARATOR);		
		// tag file support class
		require_once sprintf("%s%sgetID3-master/getid3/getid3.php", PHP_INCLUDE_PATH, DIRECTORY_SEPARATOR);
		echo sprintf("[spieldose]%s", PHP_EOL);
		echo sprintf("Reading files from path: %s%s", MUSIC_PATH, PHP_EOL);
		echo sprintf("Progress: ");
		
		$files = array();

		$i = 0;
		$chars = array("|", "/", "-", "\\");
		
		$rdi = new RecursiveDirectoryIterator(MUSIC_PATH);
		foreach (new RecursiveIteratorIterator($rdi) as $filename => $cur) {
			echo sprintf("%s\033[1D", $chars[$i++]);
			if ($i > 3) {
				$i = 0;
			}
			$extension = mb_strtolower(pathinfo($filename, PATHINFO_EXTENSION));
			// check for mime's supported on ID3 PHP Class (getid3.php) && transcoding command
			if ($extension == "mp3" || $extension == "ogg") {
				$files[] = $filename;
			}						
		}
		echo sprintf("complete (total supported files: %d)%s", count($files), PHP_EOL);

		$getID3 = new getID3();
		
		$db = new Database();
						
		foreach ($files as $file_path) {
		    echo sprintf("Processing file: %s%s", $file_path, PHP_EOL);
			$id3_obj = $getID3->analyze($file_path);
			$title = convert_to_utf8(get_tag_field_value($id3_obj, "title"));
			$artist = convert_to_utf8(get_tag_field_value($id3_obj, "artist"));
			$album = convert_to_utf8(get_tag_field_value($id3_obj, "album"));
			$album_artist = convert_to_utf8(get_tag_field_value($id3_obj, "band"));
			$year = convert_to_utf8(get_tag_field_value($id3_obj, "year"));
			$playtime_seconds = convert_to_utf8(get_tag_field_value($id3_obj, "playtime_seconds"));
			$playtime_string = convert_to_utf8(get_tag_field_value($id3_obj, "playtime_string"));
			$filesize = convert_to_utf8(get_tag_field_value($id3_obj, "filesize"));
			$sql = " INSERT OR REPLACE INTO SONG (id, path, title, artist_id, album_id, year, playtime_seconds, playtime_string, filesize) VALUES (:id, :path, :title, :artist_id, :album_id, :year, :playtime_seconds, :playtime_string, :filesize) ";
			$params = array(
				":id" => sha1($file_path),
				":path" =>$file_path,
				":title" => $title,
				":artist_id" => sha1($artist),
				":album_id" => sha1($album . $album_artist),
				":year" => $year,
				":playtime_seconds" => $playtime_seconds,
				":playtime_string" => $playtime_string,
				":filesize" => $filesize
			);			
			try {
				$db->exec($sql, $params);				
			} catch(PDOException $e) {
				echo sprintf("\tError saving SONG tags: %s%s", $e->getMessage(), PHP_EOL);
			}				
			
			if (strlen($artist) > 0) {
				$sql = " INSERT OR REPLACE INTO ARTIST (id, name) VALUES (:id, :name)";
				$params = array(
					":id" => sha1($artist),
					":name" => $artist
				);
				try {
					$db->exec($sql, $params);				
				} catch(PDOException $e) {
					echo sprintf("\tError saving ARTIST tags: %s%s", $e->getMessage(), PHP_EOL);
				}									
			}
				
			if (strlen($album) > 0) {
				$sql = " INSERT OR REPLACE INTO ALBUM (id, year, name, artist_id) VALUES (:id, :year, :name, :artist_id)";
				$params = array(
					":id" => sha1($album . $album_artist),
					":year" => $year,
					":name" => $album,
					":artist_id" => sha1($album_artist),
				);
				try {
					$db->exec($sql, $params);				
				} catch(PDOException $e) {
					echo sprintf("\tError saving ALBUM tags: %s%s", $e->getMessage(), PHP_EOL);
				}									
			}			
		}		
		$db = null;		
	}			
?>