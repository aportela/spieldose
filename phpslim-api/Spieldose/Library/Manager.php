<?php

declare(strict_types=1);

namespace Spieldose\Library;

class Manager
{
    private \aportela\DatabaseWrapper\DB $dbh;
    private \Psr\Log\LoggerInterface $logger;

    public function __construct(\aportela\DatabaseWrapper\DB $dbh, \Psr\Log\LoggerInterface $logger)
    {
        $this->dbh = $dbh;
        $this->logger = $logger;
    }

    public function __destruct() {}

    /**
     * checks for library path existence (returns path id || null)
     */
    private function getLibraryPathId(string $path): ?string
    {
        $results = $this->dbh->query(
            "
                SELECT
                    id
                FROM LIBRARY_PATH
                WHERE path = :path
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":path", realpath($path)),
            ]
        );
        if (count($results) == 1) {
            return ($results[0]->id);
        } else {
            return (null);
        }
    }

    /**
     * this is used for preventing duplicated (children) library paths (ex: adding paths: "c:\music\" && "c:\music\jazz")
     */
    public function isPathContainedOnCurrentLibraryPaths(string $path): bool
    {
        $path = realpath($path);
        $results = $this->dbh->query(
            "
                SELECT
                    path
                FROM LIBRARY_PATH
                WHERE path <> :path
                ORDER BY path
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":path", $path),
            ]
        );
        foreach ($results as $result) {
            // TODO: str_starts_with works with unicode ?
            if (str_starts_with($path, $result->path)) {
                return (true);
            }
        }
        return (false);
    }

    /**
     * add / update library path
     */
    public function addLibraryPath(string $path): string
    {
        $path = realpath($path);
        $pathId = $this->getLibraryPathId($path);
        if (empty($pathId)) {
            $pathId = \Spieldose\Utils::uuidv4();
        }
        $stat = stat($path);
        $this->logger->info("Setting library path", [$pathId, $path]);
        $this->dbh->execute(
            "
                    INSERT INTO LIBRARY_PATH
                        (id, path, ctime, mtime)
                    VALUES
                        (:id, :path, :current_timestamp, :mtime)
                    ON CONFLICT (id) DO
                    UPDATE SET
                        mtime = :mtime;
                ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":id", $pathId),
                new \aportela\DatabaseWrapper\Param\StringParam(":path", $path),
                new \aportela\DatabaseWrapper\Param\IntegerParam(":current_timestamp", intval(microtime(true) * 1000)),
                new \aportela\DatabaseWrapper\Param\IntegerParam(":mtime", $stat['mtime'])
            ]
        );
        return ($pathId);
    }

    /**
     * remove library path
     */
    public function removeLibraryPath(string $path): bool
    {
        $pathId = $this->getLibraryPathId($path);
        if (! empty($pathId)) {
            $this->logger->info("Removing library path", [$pathId, $path]);
            // DIRECTORY && FILE related rows are deleted on cascade
            $this->dbh->execute(
                "
                    DELETE FROM LIBRARY_PATH
                    WHERE id = :id
                ",
                [
                    new \aportela\DatabaseWrapper\Param\StringParam(":id", $pathId)
                ]
            );
            return (true);
        } else {
            return (false);
        }
    }

    /**
     * return all library paths
     * @return array<mixed>
     */
    public function getLibraryPaths(): array
    {
        return (
            $this->dbh->query(
                "
                SELECT
                    id, path
                FROM LIBRARY_PATH
                ORDER BY path
            "
            )
        );
    }

    /**
     * @return array<mixed>
     */
    public function getLibraryPathDirectories(string $libraryPathId): array
    {
        return (
            $this->dbh->query(
                "
                    SELECT
                        id, path
                    FROM DIRECTORY
                    WHERE library_path_id = :library_path_id
                    ORDER BY path
                ",
                [
                    new \aportela\DatabaseWrapper\Param\StringParam(":library_path_id", $libraryPathId)
                ]
            )
        );
    }

    public function getLibraryPathDirectoryId(string $path): ?string
    {
        $results = $this->dbh->query(
            "
                SELECT
                    id
                FROM DIRECTORY
                WHERE path = :path
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":path", realpath($path)),
            ]
        );
        if (count($results) == 1) {
            return ($results[0]->id);
        } else {
            return (null);
        }
    }

    /**
     * @return array<mixed>
     */
    public function getLibraryPathDirectoryFiles(string $libraryPathDirectoryId): array
    {
        return (
            $this->dbh->query(
                "
                    SELECT
                        FILE.id, DIRECTORY.path, FILE.name
                    FROM DIRECTORY
                    INNER JOIN FILE ON FILE.directory_id = DIRECTORY.id
                    WHERE DIRECTORY.id = :directory_id
                    ORDER BY path
                ",
                [
                    new \aportela\DatabaseWrapper\Param\StringParam(":directory_id", $libraryPathDirectoryId)
                ],
                function ($rows) {
                    array_map(
                        function ($item) {
                            $item->fullPath = $item->path . DIRECTORY_SEPARATOR . $item->name;
                            unset($item->name);
                            unset($item->path);
                            return $item;
                        },
                        $rows
                    );
                }
            )
        );
    }

    public function getLibraryPathDirectoryFileId(string $directoryId, string $name): ?string
    {
        $results = $this->dbh->query(
            "
                SELECT
                    id
                FROM FILE
                WHERE
                    directory_id = :directory_id
                AND
                    name = :name
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":directory_id", $directoryId),
                new \aportela\DatabaseWrapper\Param\StringParam(":name", $name),
            ]
        );
        if (count($results) == 1) {
            return ($results[0]->id);
        } else {
            return (null);
        }
    }

    public function removeLibraryPathDirectoryFile(string $id)
    {
        $this->logger->notice("Removing library path directory file", [$id]);
        $this->dbh->execute(
            "
                DELETE FROM FILE
                WHERE id = :id
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":id", $id)
            ]
        );
    }


    /**
     * scan all library paths
     */
    public function scanLibrary()
    {
        $this->logger->notice("Scanning full library");
        $paths = $this->getLibraryPaths();
        foreach ($paths as $path) {
            $this->scanLibraryPath($path->id, $path->path);
        }
    }

    /**
     * full list (id/path) of library files (used for clean orphaned data)
     * return array<mixed>
     */
    public function getAllLibraryPathDirectoryFiles(): array
    {
        return (
            $this->dbh->query(
                "
                    SELECT
                        FILE.id, DIRECTORY.path, FILE.name
                    FROM FILE
                    INNER JOIN DIRECTORY ON DIRECTORY.ID = FILE.directory_id
                    ORDER BY DIRECTORY.path, FILE.name
                ",
                [],
                function ($rows) {
                    array_map(
                        function ($item) {
                            $item->fullPath = $item->path . DIRECTORY_SEPARATOR . $item->name;
                            unset($item->name);
                            unset($item->path);
                            return $item;
                        },
                        $rows
                    );
                }
            )
        );
    }





    public function saveMBCacheArtist(\aportela\MusicBrainzWrapper\Artist $mbCache)
    {
        $this->logger->debug("Saving MusicBrainz artist cache", [$mbCache->mbId, $mbCache->name]);
        $this->dbh->execute(
            "
                INSERT INTO CACHE_ARTIST_MUSICBRAINZ
                    (mbid, name, country, ctime, mtime)
                VALUES
                    (:mbid, :name, :country, :current_timestamp, NULL)
                ON CONFLICT (mbid) DO
                    UPDATE SET
                        name = :name,
                        country = :country,
                        mtime = :current_timestamp
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $mbCache->mbId),
                new \aportela\DatabaseWrapper\Param\StringParam(":name", $mbCache->name),
                new \aportela\DatabaseWrapper\Param\StringParam(":country", $mbCache->country),
                new \aportela\DatabaseWrapper\Param\IntegerParam(":current_timestamp", intval(microtime(true) * 1000)),
            ]
        );
        $this->dbh->execute(
            "
                DELETE FROM CACHE_ARTIST_MUSICBRAINZ_GENRE
                WHERE
                    artist_mbid = :artist_mbid
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":artist_mbid", $mbCache->mbId)
            ]
        );
        foreach ($mbCache->genres as $genre) {
            $this->dbh->execute(
                "
                    INSERT INTO CACHE_ARTIST_MUSICBRAINZ_GENRE
                        (artist_mbid, genre)
                    VALUES
                        (:artist_mbid, :genre)
                ",
                [
                    new \aportela\DatabaseWrapper\Param\StringParam(":artist_mbid", $mbCache->mbId),
                    new \aportela\DatabaseWrapper\Param\StringParam(":genre", $genre)
                ]
            );
        }
        $this->dbh->execute(
            "
                DELETE FROM CACHE_ARTIST_MUSICBRAINZ_URL_RELATIONSHIP
                WHERE
                    artist_mbid = :artist_mbid
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":artist_mbid", $mbCache->mbId)
            ]
        );
        foreach ($mbCache->relations as $relation) {
            $this->dbh->execute(
                "
                    INSERT INTO CACHE_ARTIST_MUSICBRAINZ_URL_RELATIONSHIP
                        (artist_mbid, relation_type_id, name, url)
                    VALUES
                        (:artist_mbid, :relation_type_id, :name, :url)
                ",
                [
                    new \aportela\DatabaseWrapper\Param\StringParam(":artist_mbid", $mbCache->mbId),
                    new \aportela\DatabaseWrapper\Param\StringParam(":relation_type_id", $relation->typeId),
                    new \aportela\DatabaseWrapper\Param\StringParam(":name", $relation->name),
                    new \aportela\DatabaseWrapper\Param\StringParam(":url", $relation->url)
                ]
            );
        }
    }

    public function saveLastFMCacheArtist(\aportela\LastFMWrapper\Artist $lastFMCache)
    {
        $imageURL = null;
        try {
            $imageURL = $lastFMCache->getImageFromArtistPageURL($lastFMCache->url);
        } catch (\Throwable $e) {
        }
        $this->logger->debug("Saving lastFM artist cache", [$lastFMCache->mbId, $lastFMCache->name]);
        $this->dbh->execute(
            "
                INSERT INTO CACHE_ARTIST_LASTFM
                    (md5_hash, mbid, name, url, image, bio_summary, bio_content, ctime, mtime)
                VALUES
                    (:md5_hash, :mbid, :name, :url, :image, :bio_summary, :bio_content, :current_timestamp, NULL)
                ON CONFLICT (md5_hash) DO
                    UPDATE SET
                        mbid = :mbid,
                        url = :url,
                        image = :image,
                        bio_summary = :bio_summary,
                        bio_content = :bio_content,
                        mtime = :current_timestamp
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":md5_hash", md5($lastFMCache->name)),
                ! empty($lastFMCache->mbId) ?
                    new \aportela\DatabaseWrapper\Param\StringParam(":mbid", $lastFMCache->mbId) :
                    new \aportela\DatabaseWrapper\Param\NullParam(":mbId"),
                new \aportela\DatabaseWrapper\Param\StringParam(":name", $lastFMCache->name),
                new \aportela\DatabaseWrapper\Param\StringParam(":url", $lastFMCache->url),
                ! empty($imageURL) ?
                    new \aportela\DatabaseWrapper\Param\StringParam(":image", $imageURL) :
                    new \aportela\DatabaseWrapper\Param\NullParam(":image"),
                ! empty($lastFMCache->bio->summary) ?
                    new \aportela\DatabaseWrapper\Param\StringParam(":bio_summary", $lastFMCache->bio->summary) :
                    new \aportela\DatabaseWrapper\Param\NullParam(":bio_summary"),
                ! empty($lastFMCache->bio->content) ?
                    new \aportela\DatabaseWrapper\Param\StringParam(":bio_content", $lastFMCache->bio->content) :
                    new \aportela\DatabaseWrapper\Param\NullParam(":bio_content"),
                new \aportela\DatabaseWrapper\Param\IntegerParam(":current_timestamp", intval(microtime(true) * 1000)),
            ]
        );
        $this->dbh->execute(
            "
                DELETE FROM CACHE_ARTIST_LASTFM_TAG
                WHERE
                    artist_hash = :artist_hash
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":artist_hash", md5($lastFMCache->name))
            ]
        );
        foreach ($lastFMCache->tags as $tag) {
            $this->dbh->execute(
                "
                    INSERT INTO CACHE_ARTIST_LASTFM_TAG
                        (artist_hash, tag)
                    VALUES
                        (:artist_hash, :tag)
                ",
                [
                    new \aportela\DatabaseWrapper\Param\StringParam(":artist_hash", md5($lastFMCache->name)),
                    new \aportela\DatabaseWrapper\Param\StringParam(":tag", $tag)
                ]
            );
        }
        $this->dbh->execute(
            "
                DELETE FROM CACHE_ARTIST_LASTFM_SIMILAR
                WHERE
                    artist_hash = :artist_hash
            ",
            [
                new \aportela\DatabaseWrapper\Param\StringParam(":artist_hash", md5($lastFMCache->name))
            ]
        );
        foreach ($lastFMCache->similar as $similarArtist) {
            $this->dbh->execute(
                "
                    INSERT INTO CACHE_ARTIST_LASTFM_SIMILAR
                        (artist_hash, name)
                    VALUES
                        (:artist_hash, :name)
                ",
                [
                    new \aportela\DatabaseWrapper\Param\StringParam(":artist_hash", md5($lastFMCache->name)),
                    new \aportela\DatabaseWrapper\Param\StringParam(":name", $similarArtist->name),
                ]
            );
        }
    }
}
