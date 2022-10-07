<?php

declare(strict_types=1);

namespace Spieldose;

class Utils
{

    /**
     * show console progress bar (// http://snipplr.com/view/29548/)
     *
     * @param $done int Done steps
     * @param $total int Total number of steps
     * @param $size int width (chars)
     */
    public static function showProgressBar(int $done = 0, int $total = 0, int $size = 30)
    {

        static $start_time;

        // if we go over our bound, just ignore it
        if ($done > $total) return;

        if (empty($start_time)) $start_time = time();
        $now = time();

        $perc = (float)($done / $total);

        $bar = floor($perc * $size);

        $status_bar = "\r[";
        $status_bar .= str_repeat("=", intval($bar));
        if ($bar < $size) {
            $status_bar .= ">";
            $status_bar .= str_repeat(" ", intval($size - $bar));
        } else {
            $status_bar .= "=";
        }

        $disp = number_format($perc * 100, 0);

        $status_bar .= "] $disp%  $done/$total";

        $rate = ($now - $start_time) / $done;
        $left = $total - $done;
        $eta = round($rate * $left, 2);

        $elapsed = $now - $start_time;

        $status_bar .= " remaining: " . number_format($eta) . " sec.  elapsed: " . number_format($elapsed) . " sec.";

        echo "$status_bar  ";

        flush();

        // when done, send a newline
        if ($done == $total) {
            echo PHP_EOL;
        }
    }
}
