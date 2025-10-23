<?php

declare(strict_types=1);

namespace Spieldose\Library;

class FileSystemCovers
{
    private const VALID_COVER_FILENAMES_DEFAULT_PATTERN = '{cover,Cover,COVER,front,Front,FRONT}.{jpg,Jpg,JPG,jpeg,Jpeg,JPEG,png,Png,PNG}';
    private string $pattern;

    public function __construct(?string $pattern = null)
    {
        $this->pattern = empty($pattern) ? self::VALID_COVER_FILENAMES_DEFAULT_PATTERN : $pattern;
    }

    public function getCoverFilename(string $path): ?string
    {
        $coverFilename = null;
        foreach (glob($path . DIRECTORY_SEPARATOR . $this->pattern, GLOB_BRACE) as $file) {
            $coverFilename = basename(realpath($file)); // get real file "case"
            break;
        }
        return ($coverFilename);
    }
}
