<?php



    namespace Phore\Cli;

    use Phore\Di\Container\DiContainer;


    class CliController {
		const INST_MAIN = "INST_MAIN";
		
		public function __construct () {
			$this->mDiContainer = new DiContainer();
		}
		

		private $myCliGroupsByName = [];


		private $mDiContainer;
        


		public function group ($name) : CliGroup {
		    if ( ! isset ($this->myCliGroupsByName[$name]))
		        $this->myCliGroupsByName[$name] = new CliGroup($name);
		    return $this->myCliGroupsByName[$name];
        }


		
		public function setDiContainer (DiContainer $diContainer) {
			$this->mDiContainer = $diContainer;
		}
		
		
		private function printHelp () {
			echo "\nUsage Description for: ";
			echo "\n Global Shortcuts:";
			echo "\n   -h     Print this help";
			echo "\n   -f     Force (don't ask)";
			echo "\n   -vX    Verbosity level (0-9)";
			echo "\n   --     Combine multiple commands by using -- as separator";
			
			echo "\n\n Modules Configuration:";
			
			foreach ($this->myCliGroupsByName as $name => $group) {
				$group->printHelp();
			}
			echo "\n";
		}
		
		
		private static $isForce = false;
		

        /**
         * @param array|bool $mockParams
         * @throws \Exception
         */
		public function dispatch (array $mockParams=null) {
			if ($mockParams !== null)
				$args = $mockParams;
			else
				$args = $GLOBALS["argv"];



			@$arg = $args[1];
			if ($arg == "" or $arg == "-h") {
				$this->printHelp();
				return false;
			}
			
			array_shift ($args);
			if ($args[0] == "-f") {
				self::$isForce = true;
				array_shift ($args);
			}
			
			if (preg_match ("/\\-v([0-9])/", $args[0], $matches)) {
				array_shift ($args);
			}


			$allArgArr = [];
			$curArgArr = [];
			foreach ($args as $curArg) {
				if ($curArg === "--") {
					// Break multi command line
					$allArgArr[] = $curArgArr;
					$curArgArr = [];
					continue;
				}
				$curArgArr[] = $curArg;
			}

			$allArgArr[] = $curArgArr;



			foreach ($allArgArr as $curArgs) {
				$found = false;
				foreach ($this->myCliGroupsByName as $key => $group) {
					/* @var $group CliGroup */
					if ($group->isMySelectStr($curArgs[0])) {
						array_shift($curArgs);
						$group->dispatch($curArgs, $this->mDiContainer);
						$found = true;
						break;
					}

				}
				if ( ! $found)
					throw new \Exception("Unbekannte Option: {$curArgs[0]}");
			}

		}
		
		
		
		public static function AskUserContinue ($question) {
			if (self::$isForce == true)
				return true;
			echo "\n $question - Continue (n/Y)?";
			$fp = fopen("php://stdin", "r");
			$data = fgets($fp, 200);
			fclose ($fp);
			if (strtoupper(trim ($data)) != "Y") {
				echo "\nAbort!\n";
			} 
		}
		
		
		
		private static $myInstances = array();
		
		/**
		 * 
		 * Enter description here ...
		 * @param unknown_type $instance
		 * @return CliController
		 */		
		public static function GetInstance($instance=self::INST_MAIN) {
			if (!isset (self::$myInstances[$instance]))
				self::$myInstances[$instance] = new self();
			return self::$myInstances[$instance];
		}

	}
