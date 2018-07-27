<?php

    namespace Phore\Cli;

    use Phore\Di\DiCaller;

    class CliGroup {
		
		
		private $myCommands = array();
		
		private $myCliSelectStr = false;
		private $myDescription = false;
		

		
		
		
		public function __construct ($cliSelectStr) {
			$this->myCliSelectStr = $cliSelectStr;
		}


		public function description ($description) : self {
		    $this->myDescription = $description;
		    return $this;
        }

		public function command($name) : CliCommand {
            $this->myCommands[] = $cmd = new CliCommand($name);
            return $cmd;
        }


		
		public function isMySelectStr ($str) {
			if ("--".$this->myCliSelectStr == $str) 
				return true;
			return false;
		}
		
		
		public function printHelp () {
			printf ("\n\n %-30s %s", "--".$this->myCliSelectStr, $this->myDescription);
			
			foreach ($this->myCommands as $command) {
				$command->printHelp();
			}
			
		}
		
		
		public function dispatch ($args, DiCaller $diContainer) {
			if (@$args[0] == "-h" or count ($args) == 0) {
				echo "Printing Help for {$this->myCliSelectStr}\n";
				$this->printHelp();
				echo "\n";
				return false;
			}
			
			foreach ($this->myCommands as $cmd) {
				/* @var $group CliCommand */
				if ($cmd->isMySelectStr($args[0])) {
					array_shift($args);
					$cmd->dispatch($args, $diContainer);
					return true;
				}
				
			}
			throw new \Exception("Option unbekannt: {$args[0]}");
		}
	}

	
	
	