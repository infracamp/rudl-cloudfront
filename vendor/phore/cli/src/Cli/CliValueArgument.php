<?php
    /**
     * Created by PhpStorm.
     * User: matthes
     * Date: 04.05.16
     * Time: 12:58
     */

    namespace Phore\Cli;



    class CliValueArgument extends CliArgument {

        public function __construct ($name, $description, $default=null, $required=false) {
            parent::__construct($name, $description, null, $default, $required);
        }
    }
    