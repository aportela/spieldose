<?php

declare(strict_types=1);

namespace Spieldose;

class Utils
{
    /**
     * @return array<mixed>
     */
    public static function getInitialState(\Psr\Container\ContainerInterface $container): array
    {
        $settings = $container->get('settings');
        return ([
            'allowSignUp' => $settings['common']['allowSignUp'],
            'defaultResultsPage' => $settings['common']['defaultResultsPage'],
            'environment' => $settings['environment'],
            'session' => array(
                'logged' => \Spieldose\UserSession::isLogged(),
                'id' => \Spieldose\UserSession::getUserId(),
                'email' => \Spieldose\UserSession::getEmail()
            )
        ]
        );
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
        if ($done > $total) {
            return;
        }

        if (empty($start_time)) {
            $start_time = time();
        }
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

        $rate = $done > 0 ? ($now - $start_time) / $done : 0;
        $left = $total - $done;

        $eta = round($rate * $left, 2);

        $elapsed = $now - $start_time;

        if ($done != $total) {
            $status_bar .= " (" . sprintf("%d %s", ($elapsed > 3600 ? $elapsed / 3600 : ($elapsed > 60 ? $elapsed / 60 : $elapsed)), ($elapsed > 3600 ? "hours" : ($elapsed > 60 ? "minutes" : "seconds"))) . " / " . sprintf("%d %s", ($eta > 3600 ? $eta / 3600 : ($eta > 60 ? $eta / 60 : $eta)), ($eta > 3600 ? "hours" : ($eta > 60 ? "minutes" : "seconds"))) . ")";
        } else {
            $status_bar .= " (" . sprintf("%d %s", ($elapsed > 3600 ? $elapsed / 3600 : ($elapsed > 60 ? $elapsed / 60 : $elapsed)), ($elapsed > 3600 ? "hours" : ($elapsed > 60 ? "minutes" : "seconds"))) . ")";
        }

        if (!empty($extraMessage)) {
            echo sprintf("%s [%s]", $status_bar, $extraMessage);
        } else {
            echo $status_bar;
        }

        flush();

        // when done, send a newline
        if ($done == $total) {
            echo $status_bar . PHP_EOL;
        }
    }

    /**
     * https://stackoverflow.com/a/31460273
     *
     * Return a UUID (version 4) using random bytes
     * Note that version 4 follows the format:
     *     xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx
     * where y is one of: [8, 9, A, B]
     *
     * We use (random_bytes(1) & 0x0F) | 0x40 to force
     * the first character of hex value to always be 4
     * in the appropriate position.
     *
     * For 4: http://3v4l.org/q2JN9
     * For Y: http://3v4l.org/EsGSU
     * For the whole shebang: https://3v4l.org/LNgJb
     *
     * @ref https://stackoverflow.com/a/31460273/2224584
     * @ref https://paragonie.com/b/JvICXzh_jhLyt4y3
     *
     * @return string
     */
    public static function uuidv4(): string
    {
        return implode('-', [
            bin2hex(random_bytes(4)),
            bin2hex(random_bytes(2)),
            bin2hex(chr((ord(random_bytes(1)) & 0x0F) | 0x40)) . bin2hex(random_bytes(1)),
            bin2hex(chr((ord(random_bytes(1)) & 0x3F) | 0x80)) . bin2hex(random_bytes(1)),
            bin2hex(random_bytes(6))
        ]);
    }

    public static function nl2P(string $text, bool $removeDuplicated = true): string
    {
        $paragraphs = [];
        foreach (explode("\n", $text) as $paragraph) {
            if ($removeDuplicated) {
                if (!empty($paragraph)) {
                    $paragraphs[] = $paragraph = "<p>" . $paragraph . "</p>";
                }
            } else {
                $paragraphs[] = $paragraph = "<p>" . $paragraph . "</p>";
            }
        }
        return (implode(PHP_EOL, $paragraphs));
    }
}
