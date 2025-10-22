<?php

declare(strict_types=1);

namespace Spieldose\Scanner;

class FileSystemCovers
{
    private const VALID_COVER_FILENAMES_DEFAULT_PATTERN = '{cover,Cover,COVER,front,Front,FRONT}.{jpg,Jpg,JPG,jpeg,Jpeg,JPEG,png,Png,PNG}';
    private string $validCoverFilenames;

    public function __construct()
    {
        $this->validCoverFilenames = self::VALID_COVER_FILENAMES_DEFAULT_PATTERN;
    }

    public function setValidCoverFilenames(string $pattern): void
    {
        $this->validCoverFilenames = $pattern;
    }

    public function getCoverFilename(string $path): ?string
    {
        $coverFilename = null;
        foreach (glob($path . DIRECTORY_SEPARATOR . $this->validCoverFilenames ?? self::VALID_COVER_FILENAMES_DEFAULT_PATTERN, GLOB_BRACE) as $file) {
            $coverFilename = basename(realpath($file)); // get real file "case"
            break;
        }
        return ($coverFilename);
    }
}
