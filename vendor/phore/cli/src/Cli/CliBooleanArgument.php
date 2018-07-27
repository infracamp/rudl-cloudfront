<?php
    /**
     * Created by PhpStorm.
     * User: matthes
     * Date: 04.05.16
     * Time: 12:58
     */

    namespace Phore\Cli;




    /**
     * Cli Variant Arguments
     *
     * @author matthes
     *
     */
    class CliBooleanArgument extends CliArgument {
        public function __construct($name, $description) {
            parent::__construct ($name, $description, null, false, false);
        }
    }


