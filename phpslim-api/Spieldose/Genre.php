<?php

declare(strict_types=1);

namespace Spieldose;

class Genre
{
    public function __construct(string $short, array $long)
    {
    }

    public function __destruct()
    {
    }

    public static function search(\Spieldose\Database\DB $dbh, array $filter = array(), string $order = "")
    {
        $sqlOrder = "";
        if (! empty($order)) {
            $sqlOrder = " ORDER BY RANDOM() ";
        } else {
            $sqlOrder = " ORDER BY genre ASC ";
        }
        $query = sprintf(" SELECT COUNT(id) AS total, genre as name FROM FILE WHERE genre IS NOT NULL GROUP BY genre %s ", $sqlOrder);
        return($dbh->query($query));
    }
}
