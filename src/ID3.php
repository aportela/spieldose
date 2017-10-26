<?php
	declare(strict_types=1);

    namespace Spieldose;

    //require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "getID3-master/getid3/getid3.php";

    class ID3 {
        private $getID3Obj;
        public $tagData;

	    public function __construct () {
            $this->getID3Obj = new \getID3();
        }

        public function __destruct() { }

		// convert $str to UTF-8 string (if required)
		private function toUTF8($str) {
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
        public function analyze(string $filePath) {
            $this->tagData = $this->getID3Obj->analyze($filePath);
        }

		private function getTagFieldValue($id3_obj, $tag_field) {
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
				$tag_value = trim($this->toUTF8($tag_value));
			}
			return($tag_value);
		}

		private function getMusicBrainzContainerData($id3_obj, $tag_field) {
			if (isset($id3_obj['tags_html']['id3v2']) && isset($id3_obj['tags_html']['id3v2']["text"]) && isset($id3_obj['tags_html']['id3v2']["text"][$tag_field])) {
				return($id3_obj['tags_html']['id3v2']["text"][$tag_field]);
			} else {
				return(null);
			}
		}

        public function getTrackTitle(): string {
            return((string)$this->getTagFieldValue($this->tagData, "title"));
        }

        public function getTrackArtistName(): string {
            return((string)$this->getTagFieldValue($this->tagData, "artist"));
        }

        public function getAlbumArtistName(): string {
            return((string)$this->getTagFieldValue($this->tagData, "band"));
        }

        public function getAlbum(): string {
            return((string)$this->getTagFieldValue($this->tagData, "album"));
        }

        public function getGenre(): string {
            return((string)$this->getTagFieldValue($this->tagData, "genre"));
        }

        public function getTrackNumber(): string {
			$number = (string) $this->getTagFieldValue($this->tagData, "track_number");
			if (strpos($number, "/") > 0) {
				$fields = explode("/", $number);
				return(intval($fields[0]) > 0 ? $fields[0]: "");
			} else {
            	return(intval($number) > 0 ? $number: "");
			}
        }

        public function getDiscNumber(): string {
			$number = (string) $this->getTagFieldValue($this->tagData, "part_of_a_set");
            return(intval($number) > 0 ? $number: "");
        }

        public function getYear(): string {
			$year = (string) $this->getTagFieldValue($this->tagData, "year");
			$year = (strlen($year) > 4) ? substr($year, 0, 4): $year;
            return(intval($year) > 0 ? $year: "");
        }

        public function getPlaytimeSeconds(): int {
            return(ceil($this->getTagFieldValue($this->tagData, "playtime_seconds")));
        }

        public function getPlaytimeString(): string {
            return((string)$this->getTagFieldValue($this->tagData, "playtime_string"));
        }

        public function getBitRate(): int {
            return(intval($this->getTagFieldValue($this->tagData, "bitrate")));
        }

        public function getMimeType(): string {
            return((string)$this->getTagFieldValue($this->tagData, "mime_type"));
        }

		public function getMusicBrainzArtistId(): string {
			return((string)$this->getMusicBrainzContainerData($this->tagData, "MusicBrainz Artist Id"));
		}

		public function getMusicBrainzAlbumId(): string {
			return((string)$this->getMusicBrainzContainerData($this->tagData, "MusicBrainz Album Id"));
		}
	}

?>