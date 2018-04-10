<?php
    declare(strict_types=1);

    namespace Spieldose;

    use \PHPImageWorkshop\ImageWorkshop;

    class Thumbnail {
        private $url = null;

        public function __construct () { }

        public function __destruct() { }

        private static function saveTmpImage(string $url = "") {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/62.0.3202.62 Safari/537.36");
            curl_setopt($curl, CURLOPT_BINARYTRANSFER, 1);
            $image = curl_exec($curl);
            curl_close($curl);
            if ($image) {
                $tmpFilename = tempnam(sys_get_temp_dir(), "THUMB");
                if(file_exists($tmpFilename)){
                    unlink($tmpFilename);
                }
                $f = fopen($tmpFilename, 'w');
                fwrite($f, $image);
                fclose($f);
                return($tmpFilename);
            } else {
                return(null);
            }
        }

        public static function get(string $url = "") {
            if (! empty($url) && filter_var($url, FILTER_VALIDATE_URL)) {
                $hash = sha1($url);
                $dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "thumbnails" . DIRECTORY_SEPARATOR . substr($hash, 0, 1) . DIRECTORY_SEPARATOR . substr($hash, 1, 1);
                $filename = sprintf("%s.jpg", $hash);
                $thumbPath = sprintf("%s%s%s", $dir, DIRECTORY_SEPARATOR, $filename);
                if (! file_exists($thumbPath)) {
                    $tmpFile = self::saveTmpImage($url);
                    $thumb = ImageWorkshop::initFromPath($tmpFile);
                    if ($thumb->getWidth() > 640) {
                        $thumb->resizeInPixel(640, null, true);
                    }
                    $createFolders = true;
                    $backgroundColor = null;
                    $imageQuality = 95;
                    $thumb->save($dir, $filename, $createFolders, $backgroundColor, $imageQuality);
                    unlink($tmpFile);
                }
                return($thumbPath);
            } else {
                throw new \Spieldose\Exception\InvalidParamsException("url");
            }
        }

        public static function getLocal(\Spieldose\Database\DB $dbh, string $hash = "") {
            if (! empty($hash)) {
                $localPath = \Spieldose\Album::getLocalPath($dbh, $hash);
                $dir = dirname(__DIR__) . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "thumbnails" . DIRECTORY_SEPARATOR . substr($hash, 0, 1) . DIRECTORY_SEPARATOR . substr($hash, 1, 1);
                $filename = sprintf("%s.jpg", $hash);
                $thumbPath = sprintf("%s%s%s", $dir, DIRECTORY_SEPARATOR, $filename);
                if (! file_exists($thumbPath)) {
                    try {
                        $thumb = ImageWorkshop::initFromPath($localPath);
                        if ($thumb->getWidth() > 640) {
                            $thumb->resizeInPixel(640, null, true);
                        }
                        $createFolders = true;
                        $backgroundColor = null;
                        $imageQuality = 95;
                        $thumb->save($dir, $filename, $createFolders, $backgroundColor, $imageQuality);
                    } catch (\Throwable $e) {
                        // TODO: save error on log
                    }
                }
                return($thumbPath);
            } else {
                throw new \Spieldose\Exception\InvalidParamsException("url");
            }
        }

        public static function getThumbnailUrls(\Spieldose\Database\DB $dbh): array {
            $query = " SELECT DISTINCT(image) FROM MB_CACHE_ALBUM WHERE image IS NOT NULL ORDER BY RANDOM() ";
            $results = $dbh->query($query, array());
            $thumbnails = array();
            foreach($results as $result) {
                $thumbnails[] = $result->image;
            }
            return($thumbnails);
        }

    }

?>