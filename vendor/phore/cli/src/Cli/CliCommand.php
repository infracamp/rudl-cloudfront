<?php

    namespace Phore\Cli;

    use Phore\Di\DiCaller;

    class CliCommand {
		
		
		private $myCliSelectStr;
		private $myDescription;
		/**
		 * 
		 * Enter description here ...
		 * @var CliArgumentContainer
		 */
		private $myArgumentContainer;
		
		
		private $myTargetFunction;

		/**
		 * CliCommand constructor.
		 * @param $cliSelectStr
		 * @param $description
		 * @param $args array|CliArgumentContainer
		 * @param $function
		 */
		public function __construct ($cliSelectStr) {
			$this->myCliSelectStr = $cliSelectStr;

			$this->myArgumentContainer = new CliArgumentContainer();
			/**
			 * 
			 * Enter description here ...
			 * @var CliArgumentContainer
			 */
		}


		public function run (callable $fn) : self {
		    $this->myTargetFunction = $fn;
		    return $this;
        }

        public function description (string $description) : self {
		    $this->myDescription = $description;
		    return $this;
        }

        public function withString(string $argumentName, $description) : self {
		    $this->myArgumentContainer->addArgument(new CliValueArgument($argumentName, $description));
		    return $this;
        }

        public function withBool(string $argumentName, $description) : self {
		    $this->myArgumentContainer->addArgument(new CliBooleanArgument($argumentName, $description));
		    return $this;
        }

		public function isMySelectStr ($str) {
			if ("--".$this->myCliSelectStr == $str)
				return true;
			return false;
		}
		
		
		
		public function printHelp () {
			
			printf ("\n %-30s %s", "  --".$this->myCliSelectStr, $this->myDescription);
			
			$this->myArgumentContainer->printDescription();
			
		}
		
		
		public function dispatch ($args, DiCaller $diContainer) {
			if (@$args[0] == "-h") {
				$this->printHelp();
				return;
			}
			//print_r($this->myTargetFunction);
			$this->myArgumentContainer->parseArguments($args);

			$overrideInterface = [CliArgumentContainer::class=>$this->myArgumentContainer];
			foreach ($this->myArgumentContainer->getArgumentNames() as $curName) {
				$overrideInterface["{$curName}"] = $this->myArgumentContainer->getArgumentByName($curName)->getValue();
			}

			$diContainer($this->myTargetFunction, $overrideInterface);
		}

	}
