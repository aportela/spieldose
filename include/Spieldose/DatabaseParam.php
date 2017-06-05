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
        public function set($name, $value, $type): \Spieldose\DatabaseParam {
			$this->name = $name;
			$this->value = $value;
			$this->type = $type;
            return($this);
		}

        /**
        *   set NULL param
        */
		public function null(string $name): \Spieldose\DatabaseParam {
            $this->name = $name;
            $this->value = null;
            $this->type = \PDO::PARAM_NULL;
            return($this);
		}

        /**
        *   set BOOL param
        */
		public function bool(string $name, bool $value): \Spieldose\DatabaseParam {
            $this->name = $name;
            $this->value = $value;
            $this->type = \PDO::PARAM_BOOL;
            return($this);
		}

        /**
        *   set INTEGER param
        */
		public function int(string $name, int $value): \Spieldose\DatabaseParam {
            $this->name = $name;
            $this->value = $value;
            $this->type = \PDO::PARAM_INT;
            return($this);
		}

        /**
        *   set STRING param
        */
		public function str(string $name, $value): \Spieldose\DatabaseParam {
            $this->name = $name;
            $this->value = $value;
            $this->type = \PDO::PARAM_STR;
            return($this);
		}

    }

?>