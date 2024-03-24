<?php
/* 
 * ╔═══╗╔══╗╔══╗╔╗╔══╗╔═══╗╔════╗╔╗  ╔╗╔══╗╔╗ ╔╗╔═══╗ ╔════╗╔══╗╔══╗╔╗  ╔══╗
 * ║╔═╗║║╔╗║║╔═╝║║║╔═╝║╔══╝╚═╗╔═╝║║  ║║╚╗╔╝║╚═╝║║╔══╝ ╚═╗╔═╝║╔╗║║╔╗║║║  ║╔═╝
 * ║╚═╝║║║║║║║  ║╚╝║  ║╚══╗  ║║  ║╚╗╔╝║ ║║ ║╔╗ ║║╚══╗   ║║  ║║║║║║║║║║  ║╚═╗
 * ║╔══╝║║║║║║  ║╔╗║  ║╔══╝  ║║  ║╔╗╔╗║ ║║ ║║╚╗║║╔══╝   ║║  ║║║║║║║║║║  ╚═╗║
 * ║║   ║╚╝║║╚═╗║║║╚═╗║╚══╗  ║║  ║║╚╝║║╔╝╚╗║║ ║║║╚══╗   ║║  ║╚╝║║╚╝║║╚═╗╔═╝║
 * ╚╝   ╚══╝╚══╝╚╝╚══╝╚═══╝  ╚╝  ╚╝  ╚╝╚══╝╚╝ ╚╝╚═══╝   ╚╝  ╚══╝╚══╝╚══╝╚══╝
 * 
 * @Author: Joshet18
 * @Discord: Joshet18#6029
 */
namespace Tools;

use Tools\commands\CommandManager;
use Tools\Utils\Config;

class Main{
	const VERSION = "1.0.1";
    private static $instance = null;

	private Config $config;
    private MainLogger $main_logger;
	private CommandManager $command_manager;
	private string $input, $ouput;

    public static function create(string $path):self{return new self($path);}

	public static function getInstance():self{
		return self::$instance;
	}

    public function __construct(private string $path){
        if(self::$instance !== null)throw new \LogicException("Only one main instance can exist at once");
        self::$instance = $this;
        $this->main_logger = new MainLogger();
		$this->command_manager = new CommandManager($this->main_logger);
		$this->main_logger->info("Starting PocketMineTools in version v".self::VERSION);
		($config = $this->config = new Config($this->getDataPath()."Config.json", Config::DETECT, ["show-path" => true, "debug" => false, "input" => "", "output" => "", "ignore-files" => []]));
		$this->main_logger->setLogDebug((bool)$config->getNested('debug',false));
		$this->input = ((($value = $config->getNested('output',''))===''?$this->getDataPath()."Input":$value));
		$this->ouput = ((($value = $config->getNested('input',''))===''?$this->getDataPath()."Result":$value));
		foreach([$this->input,$this->ouput] as $dir){
			if(!is_dir($dir) && in_array($dir, [$this->getDataPath()."Result",$this->getDataPath()."Input"])){
				if(!mkdir($dir)){
					$this->getLogger()->error("Could not create folder ".Terminal::GOLD."{$dir}");
					$this->command_manager->dispathCommand('stop',[]);
				}
			}elseif(!is_dir($dir) && !in_array($dir, [$this->getDataPath()."Result",$this->getDataPath()."Input"])){
				$this->getLogger()->error("An error occurred while searching for the ".Terminal::GOLD."{$dir}".Terminal::RED." folder, check your ".Terminal::GOLD.$this->getDataPath()."Config.json");
				$this->command_manager->dispathCommand('stop',[]);
			}
		}
		$this->command_manager->initConsole();
    }

	public function getCommandManager():CommandManager{
		return $this->command_manager;
	}

    public function getLogger():MainLogger{
        return $this->main_logger;
    }

	public function getConfig():Config{
		return $this->config;
	}

	public function getDataPath():string{
		return $this->path.DIRECTORY_SEPARATOR;
	}

	public function getShowPath():bool{
		return $this->getConfig()->getNested('show-path',true);
	}

	public function getIgnoreFiles():array{
		return $this->getConfig()->getNested('ignore-files',[]);
	}
	
	public function getInputFolder():string{
		return $this->input.DIRECTORY_SEPARATOR;
	}

	public function getOuputFolder():string{
		return $this->ouput.DIRECTORY_SEPARATOR;
	}
}