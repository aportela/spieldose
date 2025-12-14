<?php

declare(strict_types=1);

namespace Spieldose\Library;

class ID3Wrapper
{
    private readonly \getID3 $getID3;

    private $tagData;

    public function __construct()
    {
        $this->getID3 = new \getID3();
    }


    private function analyze(string $filePath): void
    {
        $this->tagData = $this->getID3->analyze($filePath);
    }

    private function hasTags(): bool
    {
        // TODO: eval all required tags
        return ($this->tagData != null);
    }

    private function getTagFieldValue(array $id3_obj, string $tag_field): mixed
    {
        $tag_value = null;
        if (isset($id3_obj[$tag_field])) {
            $tag_value = html_entity_decode((string) $id3_obj[$tag_field]);
        } elseif (isset($id3_obj['tags_html'][$tag_field][0])) {
            $tag_value = html_entity_decode((string) $id3_obj['tags_html'][$tag_field][0]);
        } elseif (isset($id3_obj['tags_html']['id3v2'][$tag_field][0])) {
            $tag_value = html_entity_decode((string) $id3_obj['tags_html']['id3v2'][$tag_field][0]);
        } elseif (isset($id3_obj['tags_html']['id3v1'][$tag_field][0])) {
            $tag_value = html_entity_decode((string) $id3_obj['tags_html']['id3v1'][$tag_field][0]);
        } elseif (isset($id3_obj['tags_html']['vorbiscomment'])) {
            if (isset($id3_obj['tags_html']['vorbiscomment'][$tag_field][0])) {
                $tag_value = html_entity_decode((string) $id3_obj['tags_html']['vorbiscomment'][$tag_field][0]);
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

        return ($tag_value);
    }

    private function getMusicBrainzContainerData(array $id3_obj, string $tag_field)
    {
        if (isset($id3_obj['tags_html']['id3v2']) && isset($id3_obj['tags_html']['id3v2']["text"]) && isset($id3_obj['tags_html']['id3v2']["text"][$tag_field])) {
            return ($id3_obj['tags_html']['id3v2']["text"][$tag_field]);
        } else {
            return (null);
        }
    }

    // convert $str to UTF-8 string (if required)
    private function toUTF8(string $str): ?string
    {
        if ($str !== '' && $str !== '0') {
            if (mb_check_encoding($str, 'UTF-8')) {
                return $str;
            } else {
                $encoding = mb_detect_encoding($str, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
                if ($encoding !== false) {
                    return ($encoding !== 'UTF-8' ? mb_convert_encoding($str, 'UTF-8', $encoding) : $str);
                } else {
                    throw new \RuntimeException("Invalid encoding");
                }
            }
        } else {
            return (null);
        }
    }

    private function getTag(\Spieldose\Library\ID3TAGType $id3TAGType): mixed
    {
        switch ($id3TAGType) {
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
                // no break
            case \Spieldose\Library\ID3TAGType::DISC_NUMBER:
                $discNumber = intval($this->toUTF8((string) $this->getTagFieldValue($this->tagData, "part_of_a_set")));
                return ($discNumber > 0 ? $discNumber : null);
            case \Spieldose\Library\ID3TAGType::YEAR:
                $year = (string) $this->getTagFieldValue($this->tagData, "year");
                $year = intval((mb_strlen($year) > 4) ? mb_substr($year, 0, 4) : $year);
                return ($year > 0 ? $year : null);
            case \Spieldose\Library\ID3TAGType::ORIGINAL_YEAR:
                $originalYear = (string) $this->getTagFieldValue($this->tagData, "original_year");
                $originalYear = intval((mb_strlen($originalYear) > 4) ? mb_substr($originalYear, 0, 4) : $originalYear);
                return ($originalYear > 0 ? $originalYear : null);
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
                return ($this->toUTF8((string) $this->getMusicBrainzContainerData($this->tagData, "MusicBrainz Artist Id")));
            case \Spieldose\Library\ID3TAGType::MB_ALBUM_ARTIST_ID:
                return ($this->toUTF8((string) $this->getMusicBrainzContainerData($this->tagData, "MusicBrainz Album Artist Id")));
            case \Spieldose\Library\ID3TAGType::MB_RELEASE_GROUP_ID:
                return ($this->toUTF8((string) $this->getMusicBrainzContainerData($this->tagData, "MusicBrainz Release Group Id")));
            case \Spieldose\Library\ID3TAGType::MB_RELEASE_ID:
                return ($this->toUTF8((string) $this->getMusicBrainzContainerData($this->tagData, "MusicBrainz Album Id")));
            case \Spieldose\Library\ID3TAGType::MB_RELEASE_TRACK_ID:
                return ($this->toUTF8((string) $this->getMusicBrainzContainerData($this->tagData, "MusicBrainz Release Track Id")));
            default:
                return (null);
        }
    }

    public function getTagsData(string $path): mixed
    {
        $this->analyze($path);
        if ($this->hasTags()) {
            $data = new \stdClass();
            $data->trackTitle = $this->getTag(\Spieldose\Library\ID3TAGType::TITLE);
            $data->trackArtist = $this->getTag(\Spieldose\Library\ID3TAGType::TRACK_ARTIST_NAME);
            $data->albumArtist = $this->getTag(\Spieldose\Library\ID3TAGType::ALBUM_ARTIST_NAME);
            $data->trackYear = $this->getTag(\Spieldose\Library\ID3TAGType::YEAR);
            $data->trackOriginalYear = $this->getTag(\Spieldose\Library\ID3TAGType::ORIGINAL_YEAR);
            $data->trackNumber = $this->getTag(\Spieldose\Library\ID3TAGType::TRACK_NUMBER);
            $data->discNumber = $this->getTag(\Spieldose\Library\ID3TAGType::DISC_NUMBER);
            $data->playtimeSeconds = $this->getTag(\Spieldose\Library\ID3TAGType::PLAYTIME_SECONDS);
            $data->artistMBIds = [];
            $tag = $this->getTag(\Spieldose\Library\ID3TAGType::MB_ARTIST_ID);
            if (! empty($tag)) {
                if (mb_strlen((string) $tag) === 36) {
                    $data->artistMBIds = [$tag];
                } elseif (mb_strlen((string) $tag) > 36 && mb_strpos((string) $tag, "/") > 0) {
                    $mbIds = explode("/", (string) $tag);
                    foreach ($mbIds as $mbId) {
                        if (mb_strlen($mbId) === 36) {
                            $data->artistMBIds[] = $mbId;
                        } else {
                            // TODO
                        }
                    }
                }
            }

            $data->releaseArtistMBIds = [];
            $tag = $this->getTag(\Spieldose\Library\ID3TAGType::MB_ALBUM_ARTIST_ID);
            if (! empty($tag)) {
                if (mb_strlen((string) $tag) === 36) {
                    $data->releaseArtistMBIds = [$tag];
                } elseif (mb_strlen((string) $tag) > 36 && mb_strpos((string) $tag, "/") > 0) {
                    $mbIds = explode("/", (string) $tag);
                    foreach ($mbIds as $mbId) {
                        if (mb_strlen($mbId) === 36) {
                            $data->artistMBIds[] = $mbId;
                        } else {
                            // TODO
                        }
                    }
                }
            }

            $data->trackAlbum = $this->getTag(\Spieldose\Library\ID3TAGType::ALBUM);
            $releaseGroupMBId = $this->getTag(\Spieldose\Library\ID3TAGType::MB_RELEASE_GROUP_ID);
            // multiple mbids (divided by "/") not supported
            $data->releaseGroupMBId = (!empty($releaseGroupMBId) && mb_strlen((string) $releaseGroupMBId) === 36) ? $releaseGroupMBId : null;
            $releaseMBId = $this->getTag(\Spieldose\Library\ID3TAGType::MB_RELEASE_ID);
            // multiple mbids (divided by "/") not supported
            $data->releaseMBId = (!empty($releaseMBId) && mb_strlen((string) $releaseMBId) === 36) ? $releaseMBId : null;
            $releaseTrackMBId = $this->getTag(\Spieldose\Library\ID3TAGType::MB_RELEASE_TRACK_ID);
            $data->releaseTrackMBId = (!empty($releaseTrackMBId) && mb_strlen((string) $releaseTrackMBId) === 36) ? $releaseTrackMBId : null;
            // multiple mbids (divided by "/") not supported
            $genre = $this->getTag(\Spieldose\Library\ID3TAGType::GENRE);
            $data->genre = empty($genre) ? null : mb_strtolower((string) $genre);
            $data->mime = $this->getTag(\Spieldose\Library\ID3TAGType::MIME_TYPE);
            return ($data);
        } else {
            return (null);
        }
    }
}
