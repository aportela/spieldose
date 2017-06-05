<?php

    namespace Spieldose;

    class DatabaseParam
    {

        public $name;
        public $value;
        public $type;

        /**
        *   set param properties
        */
        public function set($name, $value, $type) {
			$this->name = $name;
			$this->value = $value;
			$this->type = $type;
		}

        /**
        *   set NULL param
        */
		public function null(string $name) {
            $this->name = $name;
            $this->value = null;
            $this->type = \PDO::PARAM_NULL;
		}

        /**
        *   set BOOL param
        */
		public function bool(string $name, bool $value) {
            $this->name = $name;
            $this->value = $value;
            $this->type = \PDO::PARAM_BOOL;
		}

        /**
        *   set INTEGER param
        */
		public function int(string $name, int $value) {
            $this->name = $name;
            $this->value = $value;
            $this->type = \PDO::PARAM_INT;
		}

        /**
        *   set STRING param
        */
		public function str(string $name, $value) {
            $this->name = $name;
            $this->value = $value;
            $this->type = \PDO::PARAM_STR;
		}

    }

?>