<?php

    namespace Spieldose;

    class CmdLine
    {
        private $options = array();

	    public function __construct (string $short, array $long) {
            $this->options = getopt($short, $long);
        }

        /**
        *   check for parameter existence
        */
        public function hasParam(string $param) {
            return(array_key_exists($param, $this->options));
        }

        /**
        *   get parameter value
        */
        public function getParamValue(string $key) {
            return(isset($this->options[$key]) ? $this->options[$key]: null);
        }

        public function __destruct() { }
    }

?>