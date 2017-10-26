<?php
    declare(strict_types=1);

    namespace Spieldose;

    class FileSystem {
        const VALID_FORMATS = array("mp3", "ogg");

        /**
         * get directory files (recursive)
         *
         * @params $path string path of the files
         */
        public static function getRecursiveDirectoryFiles(string $path) {
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