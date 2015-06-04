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
			$tag_value = null;
			if (isset($id3_obj[$tag_field])) {
				$tag_value = html_entity_decode($id3_obj[$tag_field]);
			} else if (isset($id3_obj['tags_html'][$tag_field][0])) {
				$tag_value = html_entity_decode($id3_obj['tags_html'][$tag_field][0]);
			} else if (isset($id3_obj['tags_html']['id3v2'][$tag_field][0])) {
				$tag_value = html_entity_decode($id3_obj['tags_html']['id3v2'][$tag_field][0]);
			} else if (isset($id3_obj['tags_html']['id3v1'][$tag_field][0])) {
				$tag_value = html_entity_decode($id3_obj['tags_html']['id3v1'][$tag_field][0]);
			} else if (isset($id3_obj['tags_html']['vorbiscomment'])) {
				if (isset($id3_obj['tags_html']['vorbiscomment'][$tag_field][0])) {
					$tag_value = html_entity_decode($id3_obj['tags_html']['vorbiscomment'][$tag_field][0]);
				}
				else {
					// try to guess not matched tags
					$tag = null;
					switch($tag_field) {
						case 'year':
							if (isset($id3_obj['tags_html']['vorbiscomment']['date'][0])) {
								$tag_value = $id3_obj['tags_html']['vorbiscomment']['date'][0];
							}
						break;
						default:
							$tag_value = null;
						break;
					}
				}
			}
			if ($tag_value != null) {
				$tag_value = trim(convert_to_utf8($tag_value));
			}
			return($tag_value);
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
			$id = sha1($file_path);
			$last_modified_time = filemtime($file_path);
			$sql = " SELECT id, last_modified_time FROM SONG WHERE id = :id ";
			$params = array(":id" => $id);
			$song = $db->fetch($sql, $params);
			// file already exists in database && is updated ?
			$update = ($song && $song->last_modified_time != $last_modified_time);
			// new file found || updated file
			if (! $song || $update) {
				$id3_obj = $getID3->analyze($file_path);
				$track_number = get_tag_field_value($id3_obj, "track"); 
				$tag_part_of_a_set = get_tag_field_value($id3_obj, "part_of_a_set");
				$title = get_tag_field_value($id3_obj, "title");
				$artist = get_tag_field_value($id3_obj, "artist");
				$album = get_tag_field_value($id3_obj, "album");
				$album_artist = get_tag_field_value($id3_obj, "band");
				$year = convert_to_utf8(get_tag_field_value($id3_obj, "year"));
				$playtime_seconds = intval(get_tag_field_value($id3_obj, "playtime_seconds"));			
				$playtime_string = get_tag_field_value($id3_obj, "playtime_string");
				$bitrate = get_tag_field_value($id3_obj, "bitrate");
				$mime = get_tag_field_value($id3_obj, "mime_type");
				if (! $update) {
					$sql = " INSERT INTO SONG (id, path, last_modified_time, mime, bitrate, tag_track_number, tag_part_of_a_set, tag_title, artist_id, album_id, tag_year, tag_playtime_seconds, tag_playtime_string) VALUES (:id, :path, :last_modified_time, :mime, :bitrate, :tag_track_number, :tag_title, :artist_id, :album_id, :tag_year, :tag_playtime_seconds, :tag_playtime_string) ";					
				} else {
					$sql = " INSERT OR UPDATE INTO SONG (id, path, last_modified_time, mime, bitrate, tag_track_number, tag_part_of_a_set, tag_title, artist_id, album_id, tag_year, tag_playtime_seconds, tag_playtime_string) VALUES (:id, :path, :last_modified_time, :mime, :bitrate, :tag_track_number, :tag_title, :artist_id, :album_id, :tag_year, :tag_playtime_seconds, :tag_playtime_string) ";

				}
				$params = array(
					":id" => $id,
					":path" =>$file_path,
					":last_modified_time" => $last_modified_time,
					":mime" => $mime,
					":bitrate" => $bitrate,
					":tag_track_number" => $track_number, 
					":tag_part_of_a_set" => $tag_part_of_a_set,
					":tag_title" => $title,
					":artist_id" => strlen($artist) > 0 ? sha1(mb_strtolower($artist)) : null,
					":album_id" => strlen($album) > 0 ? sha1(mb_strtolower($album . $album_artist . $year . $tag_part_of_a_set)) : null,
					":tag_year" => $year,
					":tag_playtime_seconds" => $playtime_seconds,
					":tag_playtime_string" => $playtime_string
				);			
				try {
					$db->exec($sql, $params);				
				} catch(PDOException $e) {
					echo sprintf("\tError saving SONG tags: %s%s", $e->getMessage(), PHP_EOL);
				}				
				
				if (strlen($artist) > 0) {
					$sql = " INSERT OR REPLACE INTO ARTIST (id, tag_name) VALUES (:id, :tag_name)";
					$params = array(
						":id" => sha1(mb_strtolower($artist)),
						":tag_name" => $artist
					);
					try {
						$db->exec($sql, $params);				
					} catch(PDOException $e) {
						echo sprintf("\tError saving ARTIST tags: %s%s", $e->getMessage(), PHP_EOL);
					}									
				}
					
				if (strlen($album) > 0 && strlen($album_artist) > 0) {
					$sql = " INSERT OR REPLACE INTO ALBUM (id, tag_year, tag_name, artist_id) VALUES (:id, :tag_year, :tag_name, :artist_id)";
					$params = array(
						":id" => sha1(mb_strtolower($album . $album_artist)),
						":tag_year" => $year,
						":tag_name" => $album,
						":artist_id" => sha1(mb_strtolower($album_artist)),
					);
					try {
						$db->exec($sql, $params);				
					} catch(PDOException $e) {
						echo sprintf("\tError saving ALBUM tags: %s%s", $e->getMessage(), PHP_EOL);
					}									
				}
			}		
		}		
		$db = null;		
	}			
?>