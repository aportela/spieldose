<?php

declare(strict_types=1);

namespace Spieldose;

class Scanner
{
    private $dbh;
    private $logger;
    private $id3;

    public function __construct(\aportela\DatabaseWrapper\DB $dbh, \Monolog\Logger $logger)
    {
        $this->dbh = $dbh;
        $this->logger = $logger;
        $this->id3 = new \Spieldose\ID3();
    }

    public function __destruct()
    {
    }

    public function scan($filePath)
    {
        $this->logger->debug("Processing file: " . $filePath);
        echo "Scanning " . $filePath . ": ";
        try {
            $this->dbh->query(
                " REPLACE INTO FILES (SHA1_HASH, PATH, NAME, ATIME, MTIME) VALUES (:id, :path, :name, STRFTIME('%s'), :mtime) ",
                array(
                    new \aportela\DatabaseWrapper\Param\StringParam(":id", sha1($filePath)),
                    new \aportela\DatabaseWrapper\Param\StringParam(":path", dirname($filePath)),
                    new \aportela\DatabaseWrapper\Param\StringParam(":name", basename($filePath)),
                    new \aportela\DatabaseWrapper\Param\IntegerParam(":mtime", filemtime($filePath))
                )
            );
            echo "ok!" . PHP_EOL;
        } catch (\Throwable $e) {
            echo "error!" . PHP_EOL;
        }
    }

    public function cleanUp()
    {
        $this->logger->debug("Starting scanner cleanup");
        $results = $this->dbh->query(
            " SELECT SHA1_HASH AS id, PATH || :directory_separator || NAME AS filePath FROM FILES ORDER BY PATH, NAME ",
            array(
                new \aportela\DatabaseWrapper\Param\StringParam(":directory_separator", DIRECTORY_SEPARATOR)
            )
        );
        foreach ($results as $result) {
            if (!file_exists($result->filePath)) {
                echo "deleting " . $result->id . PHP_EOL;
                $this->dbh->query(
                    " DELETE FROM FILES WHERE SHA1_HASH = :id ",
                    array(
                        new \aportela\DatabaseWrapper\Param\StringParam(":id", $result->id)
                    )
                );
            }
        }
    }
}
