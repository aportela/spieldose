<?php

declare(strict_types=1);

namespace Spieldose;

use PHPImageWorkshop\ImageWorkshop;

class Thumbnail
{
    public const WIDTH = 640;
    public const IMAGE_QUALITY = 95;
    public const OUTPUT_FORMAT_EXTENSION = ".jpg";
    private $url = null;

    public function __construct()
    {
    }

    public function __destruct()
    {
    }

    /**
     * get remote image & save on temporal local file
     *
     * @param string $url remote image url
     *
     * @return string temporal local file complete path (null on errors)
     */
    private static function saveRemoteImageOnTemporalPath(string $url = "")
    {
        $tmpFilename = null;
        try {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.62 Safari/537.36");
            curl_setopt($curl, CURLOPT_BINARYTRANSFER, 1);
            $image = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if ($httpCode == 200 && $image) {
                $tmpFilename = tempnam(sys_get_temp_dir(), "THUMB");
                if(file_exists($tmpFilename)) {
                    unlink($tmpFilename);
                }
                $f = fopen($tmpFilename, 'w');
                fwrite($f, $image);
                fclose($f);
            }
        } catch (\Throwable $e) {
            // TODO: save error on log
        } finally {
            if ($curl) {
                curl_close($curl);
            }
            return($tmpFilename);
        }
    }

    /**
     * get cached thumbnail local directory for selected hash
     *
     * @param string $hash local resource hash (sha1)
     *
     * @return string cached thumbnail local directory
     */
    private static function getDir(string $hash)
    {
        return(dirname(__DIR__) . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "thumbnails" . DIRECTORY_SEPARATOR . substr($hash, 0, 1) . DIRECTORY_SEPARATOR . substr($hash, 1, 1));
    }

    /**
     * get cached thumbnail local filename for selected hash
     *
     * @param string $hash local resource hash (sha1)
     *
     * @return string cached thumbnail local filename
     */
    private static function getFilename(string $hash)
    {
        return(sprintf("%s%s", $hash, self::OUTPUT_FORMAT_EXTENSION));
    }

    /**
     * get full path of thumbnail resource
     *
     * @param string $dir cached thumbnail local directory
     * @param string $filename cached thumbnail local filename
     *
     * @return string full local path of thumbnail resource
     */
    private static function getCompletePath(string $baseDirectory = "", string $filename = "")
    {
        return(sprintf("%s%s%s", $baseDirectory, DIRECTORY_SEPARATOR, $filename));
    }

    /**
     * generate cached local thumbnail from source file
     *
     * @param string $sourcePath the source file path
     * @param string $destDir the destination thumbnail directory
     * @param string destFilename the destination thumbnail filename
     *
     */
    private static function generateThumbnail(string $sourcePath = "", string $destDir = "", string $destFilename = "")
    {
        if (! empty($sourcePath) && file_exists($sourcePath)) {
            try {
                $thumb = ImageWorkshop::initFromPath($sourcePath);
                if ($thumb->getWidth() > self::WIDTH) {
                    $thumb->resizeInPixel(self::WIDTH, null, true);
                }
                $createFolders = true;
                $backgroundColor = null;
                $thumb->save($destDir, $destFilename, $createFolders, $backgroundColor, self::IMAGE_QUALITY);
            } catch (\Throwable $e) {
                // TODO: save error on log
            }
        }
    }

    /**
     * get cached local thumbnail path from remote thumbnail url
     *
     * @param string $url the remote thumbnail url
     *
     * @return string the cached local thumbnail path (null on errors)
     */
    public static function getCachedLocalPathFromUrl(string $url = "")
    {
        if (! empty($url) && filter_var($url, FILTER_VALIDATE_URL)) {
            $hash = sha1($url);
            $dir = self::getDir($hash);
            $filename = self::getFilename($hash);
            $thumbPath =self::getCompletePath($dir, $filename);
            // local cached file not found
            if (! file_exists($thumbPath)) {
                // get remote image
                $tmpFile = self::saveRemoteImageOnTemporalPath($url);
                if ($tmpFile) {
                    // generate local cached thumbnail from remote image saved in local temporal file
                    self::generateThumbnail($tmpFile, $dir, $filename);
                    unlink($tmpFile);
                } else {
                    return(null);
                }
            }
            return($thumbPath);
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("url");
        }
    }

    /**
     * get cached local thumbnail path from local folder existent image
     *
     * @param \Spieldose\Database\DB database handler
     * @param string $hash the local thumbnail hash
     *
     * @return string the cached local thumbnail path (null on errors)
     */
    public static function getCachedLocalPathFromHash(\Spieldose\Database\DB $dbh, string $hash = "")
    {
        if (! empty($hash)) {
            $localPath = \Spieldose\Album::getLocalPath($dbh, $hash);
            $dir = self::getDir($hash);
            $filename = self::getFilename($hash);
            $thumbPath =self::getCompletePath($dir, $filename);
            // local cached file not found
            if (! file_exists($thumbPath)) {
                // generate local cached thumbnail from local folder existent image
                self::generateThumbnail($localPath, $dir, $filename);
            }
            return($thumbPath);
        } else {
            throw new \Spieldose\Exception\InvalidParamsException("url");
        }
    }

    /**
     * get remote thumbnails without local cache (for re-generate thumbnail cache from commandline tools)
     *
     * @param \Spieldose\Database\DB database handler
     *
     * @return array url collection of urls not cached
     */
    public static function getThumbnailUrls(\Spieldose\Database\DB $dbh): array
    {
        $query = " SELECT DISTINCT(image) FROM MB_CACHE_ALBUM WHERE image IS NOT NULL ORDER BY RANDOM() ";
        $results = $dbh->query($query, array());
        $thumbnails = array();
        foreach($results as $result) {
            $thumbnails[] = $result->image;
        }
        return($thumbnails);
    }

}
