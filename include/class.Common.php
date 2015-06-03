<?php
	
	class Common
    {
        // serve file (with resume support)
        // kudos to Alix Axel
        // http://stackoverflow.com/a/7591130
        static function serve_file($path, $speed = null, $multipart = true) {
            while (ob_get_level() > 0) {
                ob_end_clean();
            }        
            if (is_file($path = realpath($path)) === true) {
                $file = fopen($path, 'rb');
                $size = sprintf('%u', filesize($path));
                $speed = (empty($speed) === true) ? 1024 : floatval($speed);        
                if (is_resource($file) === true) {
                    set_time_limit(0);        
                    if (strlen(session_id()) > 0) {
                        session_write_close();
                    }        
                    if ($multipart === true) {
                        $range = array(0, $size - 1);        
                        if (array_key_exists('HTTP_RANGE', $_SERVER) === true) {
                            $range = array_map('intval', explode('-', preg_replace('~.*=([^,]*).*~', '$1', $_SERVER['HTTP_RANGE'])));        
                            if (empty($range[1]) === true) {
                                $range[1] = $size - 1;
                            }                        
                            foreach ($range as $key => $value) {
                                $range[$key] = max(0, min($value, $size - 1));
                            }                        
                            if (($range[0] > 0) || ($range[1] < ($size - 1))) {
                                header(sprintf('%s %03u %s', 'HTTP/1.1', 206, 'Partial Content'), true, 206);
                            }
                        }                        
                        header('Accept-Ranges: bytes');
                        header('Content-Range: bytes ' . sprintf('%u-%u/%u', $range[0], $range[1], $size));
                    } else {
                        $range = array(0, $size - 1);
                    }                    
                    header('Pragma: public');
                    header('Cache-Control: public, no-cache');
                    header('Content-Type: application/octet-stream');
                    header('Content-Length: ' . sprintf('%u', $range[1] - $range[0] + 1));
                    header('Content-Disposition: attachment; filename="' . basename($path) . '"');
                    header('Content-Transfer-Encoding: binary');                    
                    if ($range[0] > 0) {
                        fseek($file, $range[0]);
                    }                    
                    while ((feof($file) !== true) && (connection_status() === CONNECTION_NORMAL)) {
                        echo fread($file, round($speed * 1024)); flush(); sleep(1);
                    }                    
                    fclose($file);
                }                
                exit();
            } else {
                header(sprintf('%s %03u %s', 'HTTP/1.1', 404, 'Not Found'), true, 404);
            }
            return false;
        }                        
    }

?>