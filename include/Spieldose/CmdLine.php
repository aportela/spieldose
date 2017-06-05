<?php

    namespace Spieldose;

    class CmdLine
    {
        private $options = array();

	    public function __construct (string $short, array $long) {
            $this->options = getopt($short, $long);
        }

        public function get(string $key) {
            return(isset($this->options[$key]) ? $this->options[$key]: null);
        }

        public function __destruct() { }
    }

?>