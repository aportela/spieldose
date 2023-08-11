<?php

declare(strict_types=1);

namespace Spieldose;

class FileSystem
{
    public const VALID_FORMATS = array("mp3", "ogg");

    /**
     * get directory files (recursive)
     *
     * @params $path string path of the files
     */
    public static function getRecursiveDirectoryFiles(string $path)
    {
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

    /**
     * get directory names (recursive)
     *
     * @params $path string path of the directory
     */
    public static function getRecursiveDirectories(string $path)
    {
        $directories = array($path);
        $rdi = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $path,
                \RecursiveDirectoryIterator::SKIP_DOTS
            ),
            \RecursiveIteratorIterator::SELF_FIRST,
            \RecursiveIteratorIterator::CATCH_GET_CHILD // Ignore "Permission denied"
        );
        foreach ($rdi as $path => $dir) {
            if ($dir->isDir()) {
                $directories[] = $path;
            }
        }
        return($directories);
    }

    /**
     * get mime type of image file
     */
    public static function getImageMimeSimple(string $filename)
    {
        $mime = "application/octet-stream";
        $extension = mb_strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        switch($extension) {
            case "jpg":
            case "jpeg":
                $mime = image_type_to_mime_type(IMAGETYPE_JPEG);
                break;
            case "png":
                $mime = image_type_to_mime_type(IMAGETYPE_PNG);
                break;
        }
        return($mime);
    }
}
