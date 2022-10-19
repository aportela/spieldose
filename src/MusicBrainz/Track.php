<?php

declare(strict_types=1);

namespace Spieldose\MusicBrainz;

class Track
{
    const USER_AGENT = 'Spieldose/2 (https://github.com/aportela/spieldose)';
    const API_SEARCH_URL = "http://ws.audioscrobbler.com/2.0/?method=track.search&api_key=%s&track=%s&artist=%s&limit=%d&format=json";
    const API_GET_URL_FROM_MBID = "http://ws.audioscrobbler.com/2.0/?method=track.getInfo&api_key=%s&mbid=%s&format=json";
    const API_GET_URL_FROM_TRACK_AND_ARTIST = "http://ws.audioscrobbler.com/2.0/?method=track.getInfo&api_key=%s&track=%s&artist=%s&autocorrect=1&format=json";
    public $mbId;
    public $track;
    public $artistMBId;
    public $artistName;
    public $json;

    public function __construct(string $mbId = "", string $track = "", string $artistMBId = "", string $artistName = "", string $json = "")
    {
        $this->mbId = $mbId;
        $this->track = $track;
        $this->artistMBId = $artistMBId;
        $this->artistName = $artistName;
        $this->json = $json;
    }

    public function __destruct()
    {
    }

    public static function search(string $track = "", string $artist = "", int $limit = 1): array
    {
        $results = array();
        $url = sprintf(self::API_SEARCH_URL, \Spieldose\LastFM::API_KEY, urlencode($track), urlencode($artist), $limit);
        $result = \Spieldose\Net::httpRequest($url, self::USER_AGENT);
        $result = json_decode($result);
        if (!isset($result->error)) {
            if (isset($result->results->trackmatches->track) && is_array($result->results->trackmatches->track)) {
                foreach ($result->results->trackmatches->track as $matchedTrack) {
                    $results[] = new \Spieldose\MusicBrainz\Track($matchedTrack->mbid, $matchedTrack->name, "", $matchedTrack->artist, "");
                }
            }
        } else {
            throw new \Exception("MusicBrainz error: " . $result->error);
        }
        return ($results);
    }

    public static function getFromMBId(string $mbId = "")
    {
        if (empty($mbId)) {
            throw new \Spieldose\Exception\InvalidParamsException("mbId");
        } else {
            $url = sprintf(self::API_GET_URL_FROM_MBID, \Spieldose\LastFM::API_KEY, $mbId);
            $json = \Spieldose\Net::httpRequest($url, self::USER_AGENT);
            $result = json_decode($json, false);
            if (!isset($result->error)) {
                return (new \Spieldose\MusicBrainz\Track($result->track->mbid, $result->track->name, $result->track->artist->mbid, $result->track->artist->name, $json));
            } else {
                throw new \Exception("MusicBrainz error: " . $result->error);
            }
        }
    }

    public static function getFromTrackAndArtist(string $track = "", string $artist = "")
    {
        if (empty($track)) {
            throw new \Spieldose\Exception\InvalidParamsException("track");
        } else if (empty($artist)) {
            throw new \Spieldose\Exception\InvalidParamsException("artist");
        } else {
            $url = sprintf(self::API_GET_URL_FROM_TRACK_AND_ARTIST, \Spieldose\LastFM::API_KEY, urlencode($track), urlencode($artist));
            $json = \Spieldose\Net::httpRequest($url, self::USER_AGENT);
            $result = json_decode($json, false);
            if (!isset($result->error)) {
                return (new \Spieldose\MusicBrainz\Track($result->track->mbid, $result->track->name, $result->track->artist->mbid, $result->track->artist->name, $json));
            } else {
                throw new \Exception("MusicBrainz error: " . $result->error);
            }
        }
    }

    public function save(\aportela\DatabaseWrapper\DB $dbh)
    {
        if (empty($this->mbId)) {
            throw new \Spieldose\Exception\InvalidParamsException("mbId");
        }
        if (empty($this->track)) {
            throw new \Spieldose\Exception\InvalidParamsException("track");
        }
        $params = array(
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $this->mbId),
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":track", $this->track),
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":artist_mbid", $this->artistMBId),
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":artist_mbname", $this->artistName),
            $params[] = new \aportela\DatabaseWrapper\Param\StringParam(":json", $this->json)
        );
        $dbh->exec("REPLACE INTO MB_CACHE_TRACK (MBID, TRACK, ARTIST_MBID, ARTIST_MBNAME, JSON) VALUES (:mbid, :track, :artist_mbid, :artist_mbname, :json)", $params);
    }
}
