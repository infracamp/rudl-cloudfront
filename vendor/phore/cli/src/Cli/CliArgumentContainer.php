<?php
    namespace Phore\Cli;

	class CliArgumentContainer {
		
		private $myArgs = array();	
		private $myArgsByShortCut = array();
		
		public function __construct ($arg1=false, $arg2=false) {
			if (is_array($arg1)) {
				foreach ($arg1 as $arg)
					$this->addArgument($arg);
			} elseif (is_object($arg1)) {
				foreach (func_get_args() as $arg)
					$this->addArgument($arg);
			}
		}
		
		public function addArgument (CliArgumentable $arg) {
			if (isset ($this->myArgs[$arg->getName()]))
				throw new \InvalidArgumentException("A Argument with Name {$arg->getName()} is already existing");
			$this->myArgs[$arg->getName()] = $arg;
			if ($arg->getShortCut() != false) {
				if (isset ($this->myArgsByShortCut[$arg->getShortCut()]))
					throw new \InvalidArgumentException("ShortCut '-{$arg->getShortCut()} already in use");
				$this->myArgsByShortCut[$arg->getShortCut()] = $arg;
			}
		}
		
		public function getArgumentNames () {
			return array_keys ($this->myArgs);
		}
		
		/**
		 * 
		 * Enter description here ...
		 * @param $name
		 * @throws InvalidArgumentException
		 * @return CliArgumentable
		 */
		public function getArgumentByName ($name) {
			if (!isset ($this->myArgs[$name]))
				throw new \InvalidArgumentException("Argument with Name {$name} not existing");
			return $this->myArgs[$name];
		}
		
		/**
		 * 
		 * Enter description here ...
		 * @param $name
		 * @throws InvalidArgumentException
		 * @return CliArgumentable
		 */
		public function getArgumentByShortCut ($shortCut) {
			if (!isset ($this->myArgsByShortCut[$shortCut]))
				throw new \InvalidArgumentException("ShortCut -{$shortCut} unknown");
			return $this->myArgsByShortCut[$shortCut];
		}
		
		
		public function parseArguments ($arr) {
			foreach ($arr as $cur) {
				if (preg_match ("/^\\-\\-([a-zA-Z0-9\-\_]+)=(.*)$/", $cur, $matches)) {
					if (!$this->getArgumentByName($matches[1]) instanceof CliValueArgument)
						throw new \UnexpectedValueException("Argument --{$matches[1]} is boolean");
					$this->getArgumentByName($matches[1])->setValue($matches[2]);
				} else if (preg_match ("/^\\-([a-zA-Z0-9]+)$/", $cur, $matches)) {
					if (!$this->getArgumentByName($matches[1]) instanceof CliBooleanArgument)
						throw new \UnexpectedValueException("Argument -{$matches[1]} requires value");
					$this->getArgumentByShortCut($matches[1])->setValue (true);
				} else if (preg_match ("/^\\-\\-([a-zA-Z0-9\\-\_]+)$/", $cur, $matches)) {
					if (!$this->getArgumentByName($matches[1]) instanceof CliBooleanArgument)
						throw new \UnexpectedValueException("Argument --{$matches[1]} requires value");
					$this->getArgumentByName($matches[1])->setValue(true);
				}
			}
            foreach ($this->getArgumentNames() as $argName) {
                if ($this->getArgumentByName($argName)->isPresent() == false && $this->getArgumentByName($argName)->isRequired())
                    throw new \UnexpectedValueException("Required Parameter missing: --{$argName}");
            }
		}
		
		
		public function printDescription () {
			foreach ($this->getArgumentNames() as $name) {
				$cur = $this->getArgumentByName($name);
				
				$zusatz = "";
				if ($cur instanceof CliValueArgument) {
					$zusatz = "=val";
				}
				
				printf ("\n   %-6s %-21s %s",
					$cur->getShortCut() == false ? "" : "-".$cur->getShortCut().$zusatz.",",
					"--". $cur->getName().$zusatz,
					$cur->getDescription()
				);
				
			}
		}
		
	}
	
