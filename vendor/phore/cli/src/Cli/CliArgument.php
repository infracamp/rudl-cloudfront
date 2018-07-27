<?php
    /**
     * Created by PhpStorm.
     * User: matthes
     * Date: 04.05.16
     * Time: 12:57
     */

    namespace Phore\Cli;



    class CliArgument implements CliArgumentable {
        private $myName;
        private $myDescription;
        private $myShortCut;
        private $myDefault = null;
        private $myIsRequired;
        private $myValue = null;

        public function __construct ($name, $description, $shortCut=false, $default=null, $isRequired=false) {
            $this->myName = $name;
            $this->myDescription = $description;
            $this->myShortCut = $shortCut;
            $this->myDefault = $default;
            $this->myIsRequired = $isRequired;
        }

        public function getName() {
            return $this->myName;
        }

        public function getShortCut() {
            return $this->myShortCut;
        }

        public function isRequired() {
            return $this->myIsRequired;
        }

        public function getDescription() {
            return $this->myDescription;
        }


        public function isPresent () {
            if ($this->myValue !== null)
                return true;
            return false;
        }

        public function getValue ($default=null) {
            if ($this->myValue === null && $default !== null)
                return $default;
            if ($this->myValue === null && $this->myDefault !== null)
                return $this->myDefault;
            return $this->myValue;
        }

        public function setValue ($val) {
            $this->myValue = $val;
        }
    }

