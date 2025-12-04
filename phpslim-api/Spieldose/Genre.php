<?php

declare(strict_types=1);

namespace Spieldose;

class Genre
{
    public static function search(\Spieldose\Database\DB $db, array $filter = [], string $order = "")
    {
        $sqlOrder = $order === '' || $order === '0' ? " ORDER BY genre ASC " : " ORDER BY RANDOM() ";

        $query = sprintf(" SELECT COUNT(id) AS total, genre as name FROM FILE WHERE genre IS NOT NULL GROUP BY genre %s ", $sqlOrder);
        return($db->query($query));
    }
}
