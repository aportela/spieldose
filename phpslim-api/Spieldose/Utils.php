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
     * @params $prependStr
     * @params $appendStr
     */
    public static function showProgressBar(int $done, int $total, int $size = 30, string $prependStr = "", string $appendStr = ""): void
    {
        // if we go over our bound, just ignore it
        if ($done > $total) {
            return;
        }


        static $startTimestamp;

        if (empty($startTimestamp)) {
            $startTimestamp = microtime(true);
        }

        $currentTimestamp = microtime(true);

        $percent = (float)($done / $total);

        $bar = (int) floor($percent * $size);
        $bar = min($bar, $size);
        $filled = str_repeat("=", $bar);
        $empty = str_repeat(" ", $size - $bar);
        $progressBar = "[" . $filled . ($bar < $size ? ">" : "=") . $empty . "]";

        $currentPercent = number_format($percent * 100, 0);

        $rate = $done > 0 ? ($currentTimestamp - $startTimestamp) / $done : 0;
        $left = $total - $done;

        $estimatedTimestamp = round($rate * $left, 2);
        $elapsedTimestamp = $currentTimestamp - $startTimestamp;

        // restart line cursor to begin
        $parts = ["\33[2K\r"];

        if (!empty($prependStr)) {
            $parts[] = $prependStr;
        }

        $parts[] = $progressBar;
        $parts[] = "{$currentPercent}%";
        $parts[] = "[{$done}/{$total}]";

        $formatElapsedTimestamp = function (float $seconds): string {
            if ($seconds >= 3600) {
                $value = $seconds / 3600;
                $unit = "hour";
            } elseif ($seconds >= 60) {
                $value = $seconds / 60;
                $unit = "minute";
            } else {
                $value = $seconds;
                $unit = "second";
            }
            $value = round($value);
            if ($value != 1) {
                $unit .= "s";
            }
            return "{$value} {$unit}";
        };

        if ($done != $total) {
            $parts[] = "[elapsed {$formatElapsedTimestamp($elapsedTimestamp)}, estimated {$formatElapsedTimestamp($estimatedTimestamp)}]";
        } else {
            $parts[] = "[total {$formatElapsedTimestamp($elapsedTimestamp)}]";
        }

        if (!empty($appendStr)) {
            $parts[] = $appendStr;
        }

        echo implode(" ", $parts);
        if ($done == $total) {
            echo PHP_EOL;
            $startTimestamp = null;
        }
        flush();
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
