<?php

declare(strict_types=1);

namespace Spieldose;

class File
{
    private $dbh;
    public $id;
    public $path;
    public $mime;
    public $length;

    public function __construct($container, string $id = "")
    {
        if (!$container) {
            throw new \Spieldose\Exception\InvalidParamsException("container");
        } else {
            $this->dbh = $container->get(\aportela\DatabaseWrapper\DB::class);
            $this->id = $id;
        }
    }

    public function __destruct()
    {
    }

    public function get()
    {
        $results = $this->dbh->query(
            "
                SELECT
                    (DIRECTORY.path || :directory_separator || FILE.name) AS path,
                    FILE_ID3_TAG.mime AS mime
                FROM FILE
                INNER JOIN DIRECTORY ON DIRECTORY.id = FILE.directory_id
                INNER JOIN FILE_ID3_TAG ON FILE_ID3_TAG.id = FILE.id
                WHERE FILE.id = :id
             ",
            array(
                new \aportela\DatabaseWrapper\Param\StringParam(":directory_separator", DIRECTORY_SEPARATOR),
                new \aportela\DatabaseWrapper\Param\StringParam(":id", $this->id)
            )
        );
        if (count($results) == 1) {
            $this->path = $results[0]->path;
            $this->mime = $results[0]->mime;
            $this->length = filesize($this->path);
        } else {
            throw new \Spieldose\Exception\NotFoundException($this->id);
        }
    }

    public function getData(int $offset, int $length)
    {
        if (!empty($this->path)) {
            $file = fopen($this->path, 'r');
            fseek($file, $offset);
            $data = fread($file, $length);
            fclose($file);
            return ($data);
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("path");
        }
    }
}
