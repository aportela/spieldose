<?php

    namespace Spieldose;

    class CmdLine
    {
        private $options = array();

        private $musicPath = "";

	    public function __construct () {
            $this->options = getopt("m:", array("musicPath:"));
            if (isset($this->options["m"]) && ! empty($this->options["m"])) {
                $this->setMusicPath($this->options["m"]);
            } else if (isset($this->options["musicPath"]) && ! empty($this->options["musicPath"])) {
                $this->setMusicPath($this->options["musicPath"]);
            }
        }

        private function setMusicPath(string $path) {
            $this->musicPath = $path;
        }

        public function hasMusicPath() : bool {
            return(! empty($this->musicPath));
        }

        public function getMusicPath(): string {
            return($this->musicPath);
        }

        public function __destruct() { }
    }

?>