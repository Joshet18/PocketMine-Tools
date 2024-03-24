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
namespace Tools\commands;
use Tools\{MainLogger, Main, Terminal};
use Tools\commands\defaults\Help;
use Tools\commands\defaults\MakePhar;
use Tools\commands\defaults\Query;
use Tools\commands\defaults\Stop;
use Tools\commands\defaults\UnPhar;

final class CommandManager{
    private static self $i;
    /** @var CommandBase[] */
    private array $commands = [], $alias = [];
    private ConsoleSender $console_sender;

    public static function getInstance():self{
        return self::$i;
    }

    public function __construct(private MainLogger $main_logger){
        self::$i = $this;
        $this->console_sender = new ConsoleSender();
        $this->loadCommands();
    }

    public function initConsole():void{
        $this->main_logger->info('For help, type "help" or "?"');
        for($i=PHP_INT_MIN; $i < PHP_INT_MAX; $i++){
            $args = explode(" ", $this->readLine());
            $label = $args[0] ?? '';
            $command = $this->commands[$label] ?? $this->alias[$label] ?? null;
            array_shift($args);
            if($command instanceof CommandBase){
                $command->execute($this->console_sender,$label,$args);
            }elseif($label !== '')Main::getInstance()->getLogger()->info(Terminal::RED."Unknown command: {$label}. Use help for a list of available commands.");
        }
    }

    public function dispathCommand(string $label, array $args):void{
        $command = $this->commands[$label] ?? $this->alias[$label] ?? null;
        if($command instanceof CommandBase)$command->execute($this->console_sender,$label,$args);
    }

    public function loadCommands():void{
        /** @var CommandBase $command */
        foreach([
            new Stop('stop', 'stop pocketmine-tools', null, ['exit']),
            new Help('help','get list of all commands.',null,['?']),
            new Query('query', 'get server status', null),
            new UnPhar('unphar', 'Extract .phar file', null, ['extract']),
            new MakePhar('phar', 'Make .phar file', null, ['build','make'])
        ] as $command)if(!isset($this->commands[$command->getName()])){
            $this->commands[$command->getName()] = $command;
            foreach($command->getAlias() as $alias)if(!isset($this->alias[$alias]))$this->alias[$alias] = $command;
        }
    }

    /** @return  CommandBase[] */
    public function getCommands():array{
        return $this->commands;
    }

    public function register(CommandBase $command):void{
        if(!isset($this->commands[$command->getName()])){
            $this->commands[$command->getName()] = $command;
            foreach($command->getAlias() as $alias)if(!isset($this->alias[$alias]))$this->alias[$alias] = $command;
        }
    }
    
	public function readLine() : string{
		return trim((string) fgets(STDIN));
	}
} 