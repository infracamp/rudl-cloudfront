<?php
    /**
     * Created by PhpStorm.
     * User: matthes
     * Date: 04.05.16
     * Time: 12:57
     */

    namespace Phore\Cli;


    interface CliArgumentable {

        public function getName();
        public function getShortCut();
        public function getDescription();


        public function isPresent ();

        public function getValue ();

        public function setValue ($val);

    }

    
