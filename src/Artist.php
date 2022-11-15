<?php

declare(strict_types=1);

namespace Spieldose;

class Artist
{
    public $mbid;
    public $name;
    public $bio;
    public $albums;
    public $playCount;

    public function __construct(string $name = "", array $albums = array())
    {
        $this->name = $name;
        $this->albums = $albums;
        $this->playCount = 0;
    }

    public function __destruct()
    {
    }

    /**
     * get artist metadata (bio, play stats & albums)
     *
     * @param \Spieldose\Database\DB $dbh database handler
     * name must be set
     */
    public function get(\Spieldose\Database\DB $dbh)
    {
        $this->getMusicBrainzMetadata($dbh);
        if (!empty($this->name)) {
            $params = array(
                (new \Spieldose\Database\DBParam())->str(":name", $this->name)
            );
            $query = '
                    SELECT
                        COUNT(DISTINCT(COALESCE(MBA.ARTIST, FIT.ARTIST))) AS total
                    FROM FILE F
                    LEFT JOIN FILE_ID3_TAG FIT ON FIT.ID = F.ID
                    LEFT JOIN MB_CACHE_ARTIST MBA ON MBA.MBID = F.ARTIST_MBID
                    WHERE COALESCE(MBA.artist, F.track_artist) LIKE :name
                ';
            $data = $dbh->query($query, $params);
            if ($data && $data[0]->total > 0) {
                $params[] = (new \Spieldose\Database\DBParam())->str(":user_id", \Spieldose\User::getUserId());
                $query = '
                        SELECT
                            COUNT(S.played) AS playCount
                        FROM STATS S
                        LEFT JOIN FILE F ON (F.id = S.file_id)
                        LEFT JOIN MB_CACHE_ARTIST MBA ON MBA.mbid = F.artist_mbid
                        WHERE S.user_id = :user_id
                        AND COALESCE(MBA.artist, F.track_artist) LIKE :name
                    ';
                $data = $dbh->query($query, $params);
                if ($data && $data[0]->playCount) {
                    $this->playCount = $data[0]->playCount;
                }
                $this->getMusicBrainzMetadata($dbh);
                $this->albums = (\Spieldose\Album::search($dbh, 1, 1024, array("artist" => $this->name), "year"))->results;
            } else {
                throw new \Spieldose\Exception\NotFoundException("");
            }
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("name");
        }
    }

    /**
     * get artist musicbrainz metadata (bio & image)
     *
     * @param \Spieldose\Database\DB $dbh database handler
     */
    private function getMusicBrainzMetadata(\Spieldose\Database\DB $dbh)
    {
        if (!empty($this->name)) {
            $params = array(
                (new \Spieldose\Database\DBParam())->str(":name", $this->name)
            );
            $query = sprintf('
                    SELECT DISTINCT
                        MBA.mbid,
                        MBA.bio,
                        MBA.image
                    FROM FILE F
                    LEFT JOIN MB_CACHE_ARTIST MBA ON MBA.mbid = F.artist_mbid
                    WHERE COALESCE(MBA.artist, F.track_artist) LIKE :name
                    AND MBA.mbid IS NOT NULL
                ');
            $data = $dbh->query($query, $params);
            if ($data) {
                $this->mbid = $data[0]->mbid;
                $this->bio = $data[0]->bio;
                $this->image = $data[0]->image;
            }
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("name");
        }
    }

    /**
     * ovewrite artist music brainz id
     *
     * @param \Spieldose\Database\DB $dbh database handler
     * @param string $name artist name
     * @param string $mbid artist music brainz id
     */
    public static function overwriteMusicBrainz(\Spieldose\Database\DB $dbh, string $name, string $mbid)
    {
        $mbArtist = \Spieldose\MusicBrainz\Artist::getFromMBId($mbid);
        if (!empty($mbArtist->mbId)) {
            $mbArtist->save($dbh);
            $params = array();
            $params[] = (new \Spieldose\Database\DBParam())->str(":artist_mbid", $mbArtist->mbId);
            $params[] = (new \Spieldose\Database\DBParam())->str(":track_artist", $name);
            $dbh->execute(" UPDATE FILE SET artist_mbid = :artist_mbid WHERE track_artist = :track_artist ", $params);
        }
    }

    /**
     * clear artist music brainz id
     *
     * @param \Spieldose\Database\DB $dbh database handler
     * @param string $name artist name
     */
    public static function clearMusicBrainz(\Spieldose\Database\DB $dbh, string $name)
    {
        $params = array();
        $params[] = (new \Spieldose\Database\DBParam())->str(":name", $name);
        $dbh->execute(" UPDATE FILE SET artist_mbid = NULL WHERE EXISTS (SELECT MBA.mbid FROM MB_CACHE_ARTIST MBA WHERE MBA.artist LIKE :name AND MBA.mbid = FILE.artist_mbid ) ", $params);
    }

    /**
     * search artists
     *
     * @param \Spieldose\Database\DB $dbh database handler
     * @param int $page return results from this page
     * @param int $resultsPage number of results / page
     * @param array $filter the condition filter
     * @param string $order results order
     */
    public static function search(\aportela\DatabaseWrapper\DB $dbh, \Spieldose\Helper\Pager $pager, \Spieldose\Helper\Sort $sort, array $filter = array())
    {
        $data = new \Spieldose\Helper\BrowserResults($pager, $sort, []);
        $queryParams = [];
        if (isset($filter["q"]) && !empty($filter["q"])) {
            $queryParams[] = new \aportela\DatabaseWrapper\Param\StringParam(":query", "%" . $filter["q"] . "%");
        }
        $data->items = $dbh->query(
            sprintf(
                "
                    SELECT FILE_ID3_TAG.ARTIST AS name
                    FROM FILE_ID3_TAG
                    WHERE FILE_ID3_TAG.ARTIST IS NOT NULL
                    %s
                    UNION
                    SELECT FILE_ID3_TAG.ALBUM_ARTIST AS name
                    FROM FILE_ID3_TAG
                    WHERE FILE_ID3_TAG.ALBUM_ARTIST IS NOT NULL
                    %s
                    ORDER BY name %s
                    %s
                ",
                count($queryParams) == 1 ? " AND FILE_ID3_TAG.ARTIST LIKE :query " : null,
                count($queryParams) == 1 ? " AND FILE_ID3_TAG.ALBUM_ARTIST LIKE :query " : null,
                $sort->order == \Spieldose\Helper\Sort::ASCENDING_ORDER ? \Spieldose\Helper\Sort::ASCENDING_ORDER : \Spieldose\Helper\Sort::DESCENDING_ORDER,
                $pager->resultsPage > 0 ? sprintf(" LIMIT %d, %d", $pager->getSQLQueryLimitFrom(), $pager->resultsPage) : null
            ),
            $queryParams
        );
        $data->pager->setTotalResults(count($data->items));
        if ($data->pager->currentPage > 1 || $data->pager->totalResults >= $pager->resultsPage) {
            $tmpCountResults = $dbh->query(
                sprintf(
                    "
                        SELECT COUNT(*) AS TOTAL
                        FROM (
                            SELECT FILE_ID3_TAG.ARTIST AS name
                            FROM FILE_ID3_TAG
                            WHERE FILE_ID3_TAG.ARTIST IS NOT NULL
                            %s
                            UNION
                            SELECT FILE_ID3_TAG.ALBUM_ARTIST AS name
                            FROM FILE_ID3_TAG
                            WHERE FILE_ID3_TAG.ALBUM_ARTIST IS NOT NULL
                            %s
                        )
                    ",
                    count($queryParams) == 1 ? " AND FILE_ID3_TAG.ARTIST LIKE :query " : null,
                    count($queryParams) == 1 ? " AND FILE_ID3_TAG.ALBUM_ARTIST LIKE :query " : null,
                ),
                $queryParams
            );
            if (count($tmpCountResults) == 1) {
                $data->pager->setTotalResults($tmpCountResults[0]->TOTAL);
            }
        }
        return ($data);
    }
}
