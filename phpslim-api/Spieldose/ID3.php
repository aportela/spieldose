<?php

declare(strict_types=1);

namespace Spieldose;

class ID3
{
    private $getID3Obj;
    public $tagData;

    public function __construct()
    {
        $this->getID3Obj = new \getID3();
    }

    public function __destruct()
    {
    }

    // convert $str to UTF-8 string (if required)
    private function toUTF8($str)
    {
        if ($str != null && strlen($str) > 0) {
            if (mb_detect_encoding($str, 'UTF-8', true)) {
                return ($str);
            } else {
                return (utf8_encode($str));
            }
        } else {
            return (null);
        }
    }
    public function analyze(string $filePath)
    {
        $this->tagData = $this->getID3Obj->analyze($filePath);
    }

    private function getTagFieldValue($id3_obj, $tag_field)
    {
        $tag_value = null;
        if (isset($id3_obj[$tag_field])) {
            $tag_value = html_entity_decode((string)$id3_obj[$tag_field]);
        } elseif (isset($id3_obj['tags_html'][$tag_field][0])) {
            $tag_value = html_entity_decode((string)$id3_obj['tags_html'][$tag_field][0]);
        } elseif (isset($id3_obj['tags_html']['id3v2'][$tag_field][0])) {
            $tag_value = html_entity_decode((string)$id3_obj['tags_html']['id3v2'][$tag_field][0]);
        } elseif (isset($id3_obj['tags_html']['id3v1'][$tag_field][0])) {
            $tag_value = html_entity_decode((string)$id3_obj['tags_html']['id3v1'][$tag_field][0]);
        } elseif (isset($id3_obj['tags_html']['vorbiscomment'])) {
            if (isset($id3_obj['tags_html']['vorbiscomment'][$tag_field][0])) {
                $tag_value = html_entity_decode((string)$id3_obj['tags_html']['vorbiscomment'][$tag_field][0]);
            } else {
                // try to guess not matched tags
                $tag = null;
                switch ($tag_field) {
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
            //$tag_value = trim($this->toUTF8($tag_value));
        }
        return ($tag_value);
    }

    private function getMusicBrainzContainerData($id3_obj, $tag_field)
    {
        if (isset($id3_obj['tags_html']['id3v2']) && isset($id3_obj['tags_html']['id3v2']["text"]) && isset($id3_obj['tags_html']['id3v2']["text"][$tag_field])) {
            return ($id3_obj['tags_html']['id3v2']["text"][$tag_field]);
        } else {
            return (null);
        }
    }

    public function getTrackTitle(): string
    {
        return (mb_convert_encoding((string)$this->getTagFieldValue($this->tagData, "title"), 'UTF-8', 'UTF-8'));
    }

    public function getTrackArtistName(): string
    {
        return (mb_convert_encoding((string)$this->getTagFieldValue($this->tagData, "artist"), 'UTF-8', 'UTF-8'));
    }

    public function getAlbumArtistName(): string
    {
        return (mb_convert_encoding((string)$this->getTagFieldValue($this->tagData, "band"), 'UTF-8', 'UTF-8'));
    }

    public function getAlbum(): string
    {
        return (mb_convert_encoding((string)$this->getTagFieldValue($this->tagData, "album"), 'UTF-8', 'UTF-8'));
    }

    public function getGenre(): string
    {
        return (mb_convert_encoding((string)$this->getTagFieldValue($this->tagData, "genre"), 'UTF-8', 'UTF-8'));
    }

    public function getTrackNumber(): string
    {
        $number = (string) $this->getTagFieldValue($this->tagData, "track_number");
        if (strpos($number, "/") > 0) {
            $fields = explode("/", $number);
            return (intval($fields[0]) > 0 ? $fields[0] : "");
        } else {
            return (intval($number) > 0 ? $number : "");
        }
    }

    public function getDiscNumber(): string
    {
        $number = (string) $this->getTagFieldValue($this->tagData, "part_of_a_set");
        return (intval($number) > 0 ? $number : "");
    }

    public function getYear(): string
    {
        $year = (string) $this->getTagFieldValue($this->tagData, "year");
        $year = (strlen($year) > 4) ? substr($year, 0, 4) : $year;
        return (intval($year) > 0 ? $year : "");
    }

    public function getPlaytimeSeconds(): int
    {
        return (intval(ceil((float)$this->getTagFieldValue($this->tagData, "playtime_seconds"))));
    }

    public function getPlaytimeString(): string
    {
        return ((string)$this->getTagFieldValue($this->tagData, "playtime_string"));
    }

    public function getBitRate(): int
    {
        return (intval($this->getTagFieldValue($this->tagData, "bitrate")));
    }

    public function getMimeType(): string
    {
        return ((string)$this->getTagFieldValue($this->tagData, "mime_type"));
    }

    public function getMusicBrainzArtistId(): string
    {
        return ((string)$this->getMusicBrainzContainerData($this->tagData, "MusicBrainz Artist Id"));
    }

    public function getMusicBrainzAlbumId(): string
    {
        return ((string)$this->getMusicBrainzContainerData($this->tagData, "MusicBrainz Album Id"));
    }

    public function getMusicBrainzAlbumArtistId(): string
    {
        return ((string)$this->getMusicBrainzContainerData($this->tagData, "MusicBrainz Album Artist Id"));
    }

    public function getMusicBrainzReleaseGroupId(): string
    {
        return ((string)$this->getMusicBrainzContainerData($this->tagData, "MusicBrainz Release Group Id"));
    }

    public function getMusicBrainzReleaseTrackId(): string
    {
        return ((string)$this->getMusicBrainzContainerData($this->tagData, "MusicBrainz Release Track Id"));
    }

    public function isTagged(): bool
    {
        // TODO: eval all required tags
        return ($this->tagData != null);
    }
}
