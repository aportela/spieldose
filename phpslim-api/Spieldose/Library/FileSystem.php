<?php

declare(strict_types=1);

namespace Spieldose\Library;

class FileSystem
{
    public const VALID_FORMATS = array("mp3", "ogg");
    public const VALID_COVER_FILENAMES_DEFAULT_PATTERN = '{cover,Cover,COVER,front,Front,FRONT}.{jpg,Jpg,JPG,jpeg,Jpeg,JPEG,png,Png,PNG}';

    /**
     * get directory files (recursive)
     *
     * @params $path string path of the files
     */
    public static function getRecursiveDirectoryFiles(string $path)
    {
        $files = [];
        $rdi = new \RecursiveDirectoryIterator($path);
        foreach (new \RecursiveIteratorIterator($rdi) as $filename => $cur) {
            $extension = mb_strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            if (in_array($extension, self::VALID_FORMATS)) {
                $files[] = $filename;
            }
        }
        return ($files);
    }

    public static function getDirectoryFiles($path)
    {
        return (
            // remove empty elements
            array_values(
                // return only elements with supported formats
                array_filter(glob(realpath($path) . '/*'), function ($file) {
                    if (is_file($file) && in_array(mb_strtolower(pathinfo($file, PATHINFO_EXTENSION)), self::VALID_FORMATS)) {
                        return (true);
                    } else {
                        return (false);
                    }
                })
            )
        );
    }

    /**
     * get directory names (recursive)
     *
     * @params $path string path of the directory
     * @return array<string>
     */
    public static function getRecursiveDirectories(string $path): array
    {
        $directories = [$path];
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
        return ($directories);
    }

    /**
     * check/return cover filename in selected path with (optional) custom pattern
     */
    public static function getCoverFilename(string $path, string $pattern = self::VALID_COVER_FILENAMES_DEFAULT_PATTERN): ?string
    {
        $coverFilename = null;
        foreach (glob($path . DIRECTORY_SEPARATOR . $pattern, GLOB_BRACE) as $file) {
            $coverFilename = basename(realpath($file)); // get real file "case"
            break;
        }
        return ($coverFilename);
    }

    /**
     * get mime type of image file
     */
    public static function getImageMimeType(string $filename)
    {
        $mime = "application/octet-stream";
        $extension = mb_strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        switch ($extension) {
            case "jpg":
            case "jpeg":
                $mime = image_type_to_mime_type(IMAGETYPE_JPEG);
                break;
            case "png":
                $mime = image_type_to_mime_type(IMAGETYPE_PNG);
                break;
            case "bmp":
                $mime = image_type_to_mime_type(IMAGETYPE_BMP);
                break;
        }
        return ($mime);
    }
}
