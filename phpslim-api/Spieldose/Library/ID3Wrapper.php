<?php

declare(strict_types=1);

namespace Spieldose\Library;

class ID3Wrapper
{
    private \getID3 $getID3Obj;
    public $tagData;

    public function __construct()
    {
        $this->getID3Obj = new \getID3();
    }

    public function __destruct() {}


    public function analyze(string $filePath)
    {
        $this->tagData = $this->getID3Obj->analyze($filePath);
    }

    public function hasTags(): bool
    {
        // TODO: eval all required tags
        return ($this->tagData != null);
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

    // convert $str to UTF-8 string (if required)
    private function toUTF8($str): ?string
    {
        if (! empty($str)) {
            $encoding = mb_detect_encoding($str, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
            if ($encoding === 'UTF-8') {
                return ($str);
            } else {
                return mb_convert_encoding($str, 'UTF-8', $encoding);
            }
        } else {
            return (null);
        }
    }

    public function getTag(\Spieldose\Library\ID3TAGType $tagType): mixed
    {
        switch ($tagType) {
            case \Spieldose\Library\ID3TAGType::TITLE:
                return ($this->toUTF8((string) $this->getTagFieldValue($this->tagData, "title")));
            case \Spieldose\Library\ID3TAGType::TRACK_ARTIST_NAME:
                return ($this->toUTF8((string) $this->getTagFieldValue($this->tagData, "artist")));
            case \Spieldose\Library\ID3TAGType::ALBUM_ARTIST_NAME:
                return ($this->toUTF8((string) $this->getTagFieldValue($this->tagData, "band")));
            case \Spieldose\Library\ID3TAGType::ALBUM:
                return ($this->toUTF8((string) $this->getTagFieldValue($this->tagData, "album")));
            case \Spieldose\Library\ID3TAGType::GENRE:
                return ($this->toUTF8((string) $this->getTagFieldValue($this->tagData, "genre")));
            case \Spieldose\Library\ID3TAGType::TRACK_NUMBER:
                $trackNumber = $this->toUTF8((string) $this->getTagFieldValue($this->tagData, "track_number"));
                if ($trackNumber != null && mb_strpos($trackNumber, "/") > 0) {
                    $fields = explode("/", $trackNumber);
                    $trackNumber = intval($fields[0]);
                    return ($trackNumber > 0 ? $trackNumber : null);
                } else {
                    $trackNumber = intval($trackNumber);
                    return ($trackNumber > 0 ? $trackNumber : null);
                }
            case \Spieldose\Library\ID3TAGType::DISC_NUMBER:
                $discNumber = intval($this->toUTF8((string) $this->getTagFieldValue($this->tagData, "part_of_a_set")));
                return ($discNumber > 0 ? $discNumber : null);
            case \Spieldose\Library\ID3TAGType::YEAR:
                $year = (string) $this->getTagFieldValue($this->tagData, "year");
                $year = intval((mb_strlen($year) > 4) ? mb_substr($year, 0, 4) : $year);
                return ($year > 0 ? $year : null);
            case \Spieldose\Library\ID3TAGType::PLAYTIME_SECONDS:
                $playTimeSeconds = intval($this->toUTF8((string) $this->getTagFieldValue($this->tagData, "playtime_seconds")));
                return ($playTimeSeconds > 0 ? $playTimeSeconds : null);
            case \Spieldose\Library\ID3TAGType::PLAYTIME_STRING:
                return ($this->toUTF8((string) $this->getTagFieldValue($this->tagData, "playtime_string")));
            case \Spieldose\Library\ID3TAGType::BITRATE:
                $bitrate = intval($this->toUTF8((string) $this->getTagFieldValue($this->tagData, "bitrate")));
                return ($bitrate > 0 ? $bitrate : null);
            case \Spieldose\Library\ID3TAGType::MIME_TYPE:
                return ($this->toUTF8((string) $this->getTagFieldValue($this->tagData, "mime_type")));
            case \Spieldose\Library\ID3TAGType::MB_ARTIST_ID:
                return ($this->toUTF8((string)$this->getMusicBrainzContainerData($this->tagData, "MusicBrainz Artist Id")));
            case \Spieldose\Library\ID3TAGType::MB_ALBUM_ID:
                return ($this->toUTF8((string)$this->getMusicBrainzContainerData($this->tagData, "MusicBrainz Album Id")));
            case \Spieldose\Library\ID3TAGType::MB_ALBUM_ARTIST_ID:
                return ($this->toUTF8((string)$this->getMusicBrainzContainerData($this->tagData, "MusicBrainz Album Artist Id")));
            case \Spieldose\Library\ID3TAGType::MB_RELEASE_GROUP_ID:
                return ($this->toUTF8((string)$this->getMusicBrainzContainerData($this->tagData, "MusicBrainz Release Group Id")));
            case \Spieldose\Library\ID3TAGType::MB_RELEASE_TRACK_ID:
                return ($this->toUTF8((string)$this->getMusicBrainzContainerData($this->tagData, "MusicBrainz Release Track Id")));
            default:
                return (null);
        }
    }
}
