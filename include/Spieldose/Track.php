<?php

    namespace Spieldose;

    class Track
    {

        public $id;
        public $path;

	    public function __construct (string $id = "") {
            $this->id = $id;
        }

        public function __destruct() { }

        private function exists(\Spieldose\Database $dbh): bool {
            return(true);
        }

        public function get(\Spieldose\Database $dbh) {
            if (isset($this->id) && ! empty($this->id)) {
                if ($dbh == null) {
                    $dbh = new \Spieldose\Database();
                }
                $results = $dbh->query("SELECT path FROM FILE WHERE id = :id", array(
                    (new \Spieldose\DatabaseParam())->str(":id", $this->id)
                ));
                if (count($results) == 1) {
                    $this->path = $results[0]->path;
                } else {
                    throw new \Spieldose\Exception\NotFoundException("id: " . $this->name);
                }
            } else {
                throw new \Spieldose\Exception\InvalidParamsException("id");
            }
        }
    }

?>