<?php

declare(strict_types=1);

namespace Spieldose\Library;

class FileSystem
{
    public const VALID_FORMATS = ["mp3", "ogg"];

    public const VALID_COVER_FILENAMES_DEFAULT_PATTERN = '{cover,Cover,COVER,front,Front,FRONT}.{jpg,Jpg,JPG,jpeg,Jpeg,JPEG,png,Png,PNG}';

    /**
     * get directory files (recursive)
     *
     * @params $path string path of the files
     * @return mixed[]
     */
    public static function getRecursiveDirectoryFiles(string $path): array
    {
        $files = [];
        $rdi = new \RecursiveDirectoryIterator($path);
        foreach (new \RecursiveIteratorIterator($rdi) as $filename => $cur) {
            $extension = mb_strtolower(pathinfo((string) $filename, PATHINFO_EXTENSION));
            if (in_array($extension, self::VALID_FORMATS)) {
                $files[] = $filename;
            }
        }

        return ($files);
    }

    public static function getDirectoryFiles($path): array
    {
        return (
            // remove empty elements
            array_values(
                // return only elements with supported formats
                array_filter(glob(realpath($path) . '/*'), function ($file): bool {
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
    public static function getImageMimeType(string $filename): string
    {
        $mime = "application/octet-stream";
        $extension = mb_strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return (match ($extension) {
            "jpg", "jpeg" => image_type_to_mime_type(IMAGETYPE_JPEG),
            "png" => image_type_to_mime_type(IMAGETYPE_PNG),
            "bmp" => image_type_to_mime_type(IMAGETYPE_BMP),
            default => $mime,
        });
    }
}
