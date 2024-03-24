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
namespace Tools\commands\defaults;
use Tools\commands\{CommandBase, ConsoleSender};
use Tools\{Terminal, Main};
class Help extends CommandBase {

    public function execute(ConsoleSender $sender, string $label, array $args){
        foreach(Main::getInstance()->getCommandManager()->getCommands() as $command)$sender->sendMessage(Terminal::GREEN."/{$command->getName()}".Terminal::GRAY.": ".Terminal::DARK_GREEM.($command->getDescription()??""));
    }
}