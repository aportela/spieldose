<?php

    namespace Spieldose;

    class FileSystem
    {
        const VALID_FORMATS = array("mp3", "ogg");

        public static function getRecursiveDirectoryFiles($path) {
            $files = array();
            $rdi = new \RecursiveDirectoryIterator($path);
            foreach (new \RecursiveIteratorIterator($rdi) as $filename => $cur) {
                $extension = mb_strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                if (in_array($extension, \Spieldose\FileSystem::VALID_FORMATS)) {
                    $files[] = $filename;
                }
            }
            return($files);
        }
    }

?>