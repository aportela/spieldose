<?php

declare(strict_types=1);

namespace Spieldose;

class Utils
{
    public static function getInitialState($container): array
    {
        $settings = $container->get('settings');
        return ([
            'locale' => $settings['common']['locale'],
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

        $rate = ($now - $start_time) / $done;
        $left = $total - $done;

        $eta = round($rate * $left, 2);

        $elapsed = $now - $start_time;



        $status_bar .= " remaining: " . sprintf("%d %s", ($eta > 3600 ? $eta / 3600 : ($eta > 60 ? $eta / 60 : $eta)), ($eta > 3600 ? "hours" : ($eta > 60 ? "minutes" : "seconds")))  . " elapsed: " . sprintf("%d %s", ($elapsed > 3600 ? $elapsed / 3600 : ($elapsed > 60 ? $elapsed / 60 : $elapsed)), ($elapsed > 3600 ? "hours" : ($elapsed > 60 ? "minutes" : "seconds")));

        if (!empty($extraMessage)) {
            echo "$status_bar " . sprintf(" [%s]", $extraMessage);
        } else {
            echo "$status_bar  ";
        }

        flush();

        // when done, send a newline
        if ($done == $total) {
            echo "$status_bar\n";
        }
    }

    public static function uuidv4(): string
    {
        return ((\Ramsey\Uuid\Uuid::uuid4())->toString());
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
