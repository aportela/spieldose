<?php

    namespace Spieldose;

    class Page
    {
	    public function __construct () { }

        public function __destruct() { }

        public function render() {
            if (\Spieldose\User::isLogged()) {
                readfile("templates/app.html");
            } else {
                readfile("templates/signin.html");
            }
        }
    }

?>