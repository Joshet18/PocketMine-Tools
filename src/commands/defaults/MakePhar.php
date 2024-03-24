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
use Tools\Utils\Config;
use Tools\{Terminal, Main};
class MakePhar extends CommandBase {

    public function execute(ConsoleSender $sender, string $label, array $args){
        $override = (isset($args[1]) ? $args[1] : 'n');
        if(!isset($args[0])){
            $sender->sendMessage(Terminal::RED."Usage: /{$label} <directory> [override: y|n]");
            return;
        }
        try{
            $this->BuilPhar($sender,$args[0],in_array(strtolower($override),['y','yes']));
        }catch(\Throwable $e){
            $sender->sendMessage(Terminal::RED.$e->getMessage());
        }
    }

    private function getPluginYml(string $path):?Config{
        return is_file($path."plugin.yml") ? new Config($path."plugin.yml") : null;
    }

    /**
     * @throws Exception
     */
    private function BuilPhar(ConsoleSender $sender, string $name, bool $override = false){
        $start = microtime(true);
        $result = Main::getInstance()->getOuputFolder();
        $input = Main::getInstance()->getInputFolder();
        $request = $input.$name;
        if(!is_dir($request))return throw new \Exception("{$name} is not a valid directory");
        $request .= DIRECTORY_SEPARATOR;
        $pluginYml = $this->getPluginYml($request);
        if(!$pluginYml)return throw new \Exception("Could not find plugin.yml in {$request}");
        if(count($pluginYml->getAll()) === 0)return throw new \Exception("plugin.yml has no data");
        $pharName = str_replace(" ", "", "$name.phar");
        if(file_exists($result.$pharName)){
            if(isset($override)){
                $sender->sendMessage('Overwriting phar file...');
                try{
                    \Phar::unlinkArchive($result.$pharName);
                }catch(\PharException){
                    unlink($result.$pharName);
                }
            }else{
                return throw new \Exception("$name.phar already exists in ouput folder");
            }
        }
        $sender->sendMessage('Adding files...');
        $files = [];
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($request)) as $path => $file) {
            $bool = true;
            foreach(Main::getInstance()->getIgnoreFiles() as $exclusion){
                if(str_contains($path, $exclusion)){
                    $bool = false;
                    break;
                }
            }
            if(!$bool || !$file->isFile())continue;
            if(!is_string($string = str_replace($request, "", $path)))continue;
            if(Main::getInstance()->getShowPath())$sender->sendMessage(Terminal::GREEN.$string);
            $files[$string] = $path;
        }
        $sender->sendMessage(Terminal::GOLD.'Compressing...');
        $phar = new \Phar($result."$name.phar");
        $phar->startBuffering();
        $phar->setSignatureAlgorithm(\Phar::SHA1);
        $phar->setMetadata($pluginYml->getAll());
        $count = count($phar->buildFromIterator(new \ArrayIterator($files)));
        foreach($phar as $file => $finfo){
            /** @var \PharFileInfo $finfo */
            if($finfo->getSize() > (1024 * 512)){
                $sender->sendMessage(Terminal::GOLD."Compressing ".$finfo->getFilename());
                $finfo->compress(\Phar::GZ);
            }
        }
        $phar->compressFiles(\Phar::GZ);
        $phar->stopBuffering();
        $sender->sendMessage(Terminal::GRAY.str_repeat("--", 30));
        $sender->sendMessage(Terminal::LIGHT_PURPLE."Phar Build success, {$count} files");
        $sender->sendMessage(Terminal::LIGHT_PURPLE."done in ".round(microtime(true) - $start, 1)."s");
        $sender->sendMessage(Terminal::GRAY.str_repeat("--", 30));
        $sender->sendMessage(Terminal::GOLD."Plugin.yml Info".Terminal::DARK_GRAY.":");
        foreach($pluginYml->getAll() as $k => $v)if(!is_array($v))$sender->sendMessage(Terminal::PURPLE.$k.Terminal::DARK_GRAY.":".Terminal::RESET.$v);
        $sender->sendMessage(Terminal::GRAY.str_repeat("--", 30));
    }
}