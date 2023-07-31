<?php

declare(strict_types=1);

namespace Spieldose;

class Utils
{

    public static function setAppDefaults()
    {
        session_start();
        if (DEBUG) {
            error_reporting(E_ALL);
            ini_set("display_errors", "1");
        }
    }


    /**
     * show console progress bar (// http://snipplr.com/view/29548/)
     *
     * @params $done
     * @params $total
     * @params $size
     */
    public static function showProgressBar($done, $total, $size = 30, string $extraMessage = ""): void
    {

        static $start_time;

        // if we go over our bound, just ignore it
        if ($done > $total) return;

        if (empty($start_time)) $start_time = time();
        $now = time();

        $perc = (float)($done / $total);

        $bar = floor($perc * $size);

        $status_bar = "\33[2K\r[";
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

        $status_bar .= " remaining: " . number_format($eta) . " sec. elapsed: " . number_format($elapsed) . " sec.";

        if (!empty($extraMessage)) {
            $status_bar .= sprintf(" [%s]", $extraMessage);
        }

        echo "$status_bar  ";

        flush();

        // when done, send a newline
        if ($done == $total) {
            echo "\n";
        }
    }

    public static function uuidv4(): string
    {
        return ((\Ramsey\Uuid\Uuid::uuid4())->toString());
    }
}
