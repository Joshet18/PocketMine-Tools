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
class UnPhar extends CommandBase {

    public function execute(ConsoleSender $sender, string $label, array $args){
        $override = (isset($args[1]) ? $args[1] : 'n');
        if(!isset($args[0])){
            $sender->sendMessage(Terminal::RED."Usage: /{$label} <phar> [override: y|n]");
            return;
        }
        try{
            $this->unPhar($sender,$args[0],in_array(strtolower($override),['y','yes']));
        }catch(\Throwable $e){
            $sender->sendMessage(Terminal::RED.$e->getMessage());
        }
    }

    /**
     * @throws Exception
     */
    private function unPhar(ConsoleSender $sender, string $name, bool $override = false){
        $start = microtime(true);
        $result = Main::getInstance()->getOuputFolder().$name;
        $folderPath = Main::getInstance()->getInputFolder().$name.".phar";
        if(!file_exists($folderPath))return throw new \Exception("$name.phar does not exist in input folder");
        if(file_exists($result)){
            if($override){
                $sender->sendMessage('Overwriting directory...');
                try{
                    $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($result, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
                    foreach($files as $file){
                        if($file->isFile()){
                            @unlink($file->getRealPath());
                        }else{
                            @rmdir($file->getRealPath());
                        }
                    }
                    @rmdir($result);
                }catch(\Throwable $e){
                     return throw new \Exception("Overwriting directory error has occurred: ".$e->getMessage());
                }
            }else return throw new \Exception("$name already exists in ouput folder");
        }
        $sender->sendMessage('Adding files in ouput folder...');
        if(!is_dir($result))mkdir($result);
        $pharPath = "phar://".$folderPath;
        $files = 0;
        foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($pharPath)) as $fInfo){
            $path = $fInfo->getPathname();
            @mkdir(dirname($result.str_replace($pharPath, "", $path)), 0755, true);
            file_put_contents($result.str_replace($pharPath, "", $path), file_get_contents($path));
            if(Main::getInstance()->getShowPath())$sender->sendMessage(Terminal::GREEN.$name.str_replace($pharPath, "", $path));
            $files++;
        }
        $sender->sendMessage(Terminal::GRAY.str_repeat("--", 30));
        $sender->sendMessage(Terminal::LIGHT_PURPLE."Extract Phar success, {$files} files");
        $sender->sendMessage(Terminal::LIGHT_PURPLE."done in ".round(microtime(true) - $start, 1)."s");
        $sender->sendMessage(Terminal::GRAY.str_repeat("--", 30));
    }
}