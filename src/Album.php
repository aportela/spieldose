<?php

declare(strict_types=1);

namespace Spieldose;

class Album
{

    public function __construct()
    {
    }

    public function __destruct()
    {
    }

    /**
     * search albums
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
                    SELECT FILE_ID3_TAG.ALBUM AS name, FILE_ID3_TAG.ARTIST AS artist, FILE_ID3_TAG.ALBUM_ARTIST AS albumArtist, FILE_ID3_TAG.YEAR AS year
                    FROM FILE_ID3_TAG
                    WHERE FILE_ID3_TAG.ALBUM IS NOT NULL
                    GROUP BY FILE_ID3_TAG.ALBUM, COALESCE(FILE_ID3_TAG.ALBUM_ARTIST, FILE_ID3_TAG.ARTIST), FILE_ID3_TAG.YEAR
                    %s
                    ORDER BY name %s
                    %s
                ",
                count($queryParams) == 1 ? " AND FILE_ID3_TAG.ALBUM LIKE :query " : null,
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
                            SELECT FILE_ID3_TAG.ALBUM AS name, FILE_ID3_TAG.ARTIST AS artist, FILE_ID3_TAG.ALBUM_ARTIST AS albumArtist, FILE_ID3_TAG.YEAR AS year
                            FROM FILE_ID3_TAG
                            WHERE FILE_ID3_TAG.ALBUM IS NOT NULL
                            GROUP BY FILE_ID3_TAG.ALBUM, COALESCE(FILE_ID3_TAG.ALBUM_ARTIST, FILE_ID3_TAG.ARTIST), FILE_ID3_TAG.YEAR
                            %s
                        )
                    ",
                    count($queryParams) == 1 ? " AND FILE_ID3_TAG.ALBUM LIKE :query " : null,
                ),
                $queryParams
            );
            if (count($tmpCountResults) == 1) {
                $data->pager->setTotalResults($tmpCountResults[0]->TOTAL);
            }
        }
        return ($data);
    }

    /**
     * save local album cover reference
     *
     * @param \Spieldose\Database\DB $dbh database handler
     * @param string $path directory path
     * @param string $filename album cover filename
     */
    public static function saveLocalAlbumCover(\Spieldose\Database\DB $dbh, string $path = "", string $filename = "")
    {
        /*
            if (! empty($path) && file_exists($path)) {
                if (! empty($filename) && file_exists($path . DIRECTORY_SEPARATOR . $filename)) {
                    $params = array(
                        (new \Spieldose\Database\DBParam())->str(":id", sha1($path)),
                        (new \Spieldose\Database\DBParam())->str(":base_path", $path),
                        (new \Spieldose\Database\DBParam())->str(":file_name", $filename),
                    );
                    return($dbh->execute("REPLACE INTO LOCAL_PATH_ALBUM_COVER (id, base_path, file_name) VALUES (:id, :base_path, :file_name);", $params));
                } else {
                    throw new \Spieldose\Exception\InvalidParamsException("filename");
                }
            } else {
                throw new \Spieldose\Exception\InvalidParamsException("path");
            }
            */
    }

    public static function getLocalPath(\Spieldose\Database\DB $dbh, string $id = "")
    {
        /*
            $results = $dbh->query(" SELECT base_path AS directory, file_name AS filename FROM LOCAL_PATH_ALBUM_COVER WHERE id = :id ", array(
                (new \Spieldose\Database\DBParam())->str(":id", $id)
            ));
            if (count($results) > 0) {
                return($results[0]->directory . DIRECTORY_SEPARATOR . $results[0]->filename);
            } else {
                throw new \Spieldose\Exception\NotFoundException("");
            }
            */
    }

    /**
     * get album cover collection
     */
    public static function getRandomAlbumCovers(\Spieldose\Database\DB $dbh, int $count = 32)
    {
        $results = $dbh->query(
            "
                    SELECT TMP.* FROM (
                        SELECT LOCAL_PATH_ALBUM_COVER.id AS hash, NULL AS url FROM LOCAL_PATH_ALBUM_COVER
                        UNION
                        SELECT NULL as hash, MB_CACHE_ALBUM.image AS url FROM MB_CACHE_ALBUM
                    ) TMP
                    ORDER BY RANDOM()
                    LIMIT :count
                ",
            array(
                (new \Spieldose\Database\DBParam())->int(":count", $count)
            )
        );
        return ($results);
    }
}
