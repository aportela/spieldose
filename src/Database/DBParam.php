<?php

      declare(strict_types=1);

      namespace Spieldose\Database;

      /**
       * Simple PDO Database Param Warapper
       */
      class DBParam {

            public $name;
            public $value;
            public $type;

            /**
             * set param properties
             *
             * @param $name
             * @param $value
             * @param $type
             *
             * @return \Spieldose\Database\DBParam
             */
            public function set($name, $value, $type): \Spieldose\Database\DBParam {
                  $this->name = $name;
                  $this->value = $value;
                  $this->type = $type;
                  return($this);
            }

            /**
             * set NULL param
             *
             * @param $name
             *
             * @return \Spieldose\Database\DBParam
             */
            public function null(string $name): \Spieldose\Database\DBParam {
                  $this->name = $name;
                  $this->value = null;
                  $this->type = \PDO::PARAM_NULL;
                  return($this);
            }

            /**
             * set BOOL param
             *
             * @param $name string
             * @param $value boolean
             *
             * @return \Spieldose\Database\DBParam
             */
            public function bool(string $name, bool $value): \Spieldose\Database\DBParam {
                  $this->name = $name;
                  $this->value = $value;
                  $this->type = \PDO::PARAM_BOOL;
                  return($this);
            }

            /**
             * set INTEGER param
             *
             * @param $name string
             * @param $value int
             *
             * @return \Spieldose\Database\DBParam
             */
            public function int(string $name, int $value): \Spieldose\Database\DBParam {
                  $this->name = $name;
                  $this->value = $value;
                  $this->type = \PDO::PARAM_INT;
                  return($this);
            }

            /**
             * set STRING param
             *
             * @param $name string
             * @param $value int
             *
             * @return \Spieldose\Database\DBParam
             */
            public function str(string $name, $value): \Spieldose\Database\DBParam {
                  $this->name = $name;
                  $this->value = $value;
                  $this->type = \PDO::PARAM_STR;
                  return($this);
            }
      }

?>