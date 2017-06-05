<?php

    namespace Spieldose;

    class Utils
    {

        public static function setAppDefaults() {
            session_start();
            if (DEBUG) {
                error_reporting(E_ALL);
                ini_set("display_errors", 1);
            }
        }

    }
?>