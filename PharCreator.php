<?php
function colors(string $type = "WHITE"){
	$color = fn(int $code) => "\x1b[38;5;{$code}m";
	switch($type){
		case "BACK":
			return $color(16);
		break;
		case "DARK-BLUE":
			return $color(19);
		break;
		case "DARK-GREEM":
			return $color(34);
		break;
		case "DARK-AQUA":
			return $color(37);
		break;
		case "DARK-RED":
			return $color(124);
		break;
		case "PURPLE":
			return $color(127);
		break;
		case "GOLD":
			return $color(214);
		break;
		case "GRAY":
			return $color(145);
		break;
		case "DARK-GRAY":
			return $color(59);
		break;
		case "BLUE":
			return $color(63);
		break;
		case "GREEN":
			return $color(83);
		break;
		case "AQUA":
			return $color(87);
		break;
		case "RED":
			return $color(203);
		break;
        case "ORANGE":
            return $color(202);
        break;
		case "LIGHT-PURPLE":
			return $color(207);
		break;
		case "YELLOW":
			return $color(227);
		break;
		case "WHITE":
			return $color(231);
		break;
		case "MINECOIN-GOLD":
			return $color(184);
		break;
		default:
			return $color(231);
		break;
	}
}
function info(string $message){
    echo colors("GOLD")."[".colors("DARK-AQUA").gmdate("H:i:s", time()).colors("GOLD")."] [".colors("AQUA")."PharCreator/INFO".colors("GOLD")."] ".colors("").": ".$message.colors("").PHP_EOL;
}
function warn(string $message){
    echo colors("GOLD")."[".colors("DARK-AQUA").gmdate("H:i:s", time()).colors("GOLD")."] [".colors("ORANGE")."PharCreator/WARN".colors("GOLD")."] ".colors("YELLOW").": ".$message.colors("").PHP_EOL;
}
function error(string $message){
    echo colors("GOLD")."[".colors("DARK-AQUA").gmdate("H:i:s", time()).colors("GOLD")."] [".colors("DARK-RED")."PharCreator/ERROR".colors("GOLD")."] ".colors("RED").": ".$message.colors("").PHP_EOL;
}

function BuilPhar(string $label, array $opts): Generator {
    $start = microtime(true);
    $config = file_exists(getcwd()."\Config.json") ? json_decode(file_get_contents(getcwd()."\Config.json"), true) : ["ignore-files" => [".idea", ".gitignore", "composer.json", "composer.lock", ".git", "composer.phar"], "show-path" => true, "input" => "", "output" => ""];
    if(!isset($opts['name']) or ($name = $opts['name']) === ""){
        yield colors("RED")."Usage: {$label} <directory> [override: y|n]";
        return;
    }
    $result = ($config['output'] === "" ? $config['output'] : getcwd().DIRECTORY_SEPARATOR."Result");
    $result .= DIRECTORY_SEPARATOR;
    $input = ($config['input'] === "" ? $config['input'] : getcwd().DIRECTORY_SEPARATOR."Input");
    $input .= DIRECTORY_SEPARATOR;
    $request = $input.$name;
    if(!is_dir($request)){
        yield "e>;{$name} is not a valid directory";
        return;
    }
    $request .= DIRECTORY_SEPARATOR;
    $array = readPluginYml($request);
    if(isset($array["error"])){
        switch($array["error"]){
            case "missing-plugin.yml":
                yield "e>;Could not find plugin.yml in {$request}";
            break;
            case "plugin.yml-not-data":
                yield "e>;Plugin.yml has no data";
            break;
            case "invalid-version":
                yield "e>;Invalid version '".$array['version']."', should contain at least 2 version digits";
            break;
        }
        return;
    }
    $pharName = str_replace(" ", "", "$name.phar");

    if(file_exists($result.$pharName)){
        if(isset($opts["override"]) && $opts["override"] === "y"){
            yield 'Overwriting phar file...';
            try{
                Phar::unlinkArchive($result.$pharName);
            }catch(PharException){
                unlink($result.$pharName);
            }
        }else{
            yield "e>;$name.phar already exists";
            return;
        }
    }
    yield 'Adding files...';
    $files = [];
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($request)) as $path => $file) {
        $bool = true;
        foreach ($config['ignore-files'] as $exclusion) {
            if (str_contains($path, $exclusion)) {
                $bool = false;
                break;
            }
        }
        if(!$bool || !$file->isFile()){
            continue;
        }
        if(!is_string($string = str_replace($request, "", $path))){
            continue;
        }
        if($config["show-path"])yield colors("GREEN").$string;
        $files[$string] = $path;
    }
    yield colors("GOLD").'Compressing...';
    $phar = new Phar($result."$name.phar");
    $phar->startBuffering();
    $phar->setSignatureAlgorithm(Phar::SHA1);
    $phar->setMetadata($array);
    $count = count($phar->buildFromIterator(new ArrayIterator($files)));
    yield colors("GRAY").str_repeat("--", 30);
    yield colors("LIGHT-PURPLE")."Phar Build success, {$count} files, done in ".round(microtime(true) - $start, 1)."s";
    yield colors("GRAY").str_repeat("--", 30);
    $phar->compressFiles(Phar::GZ);
    $phar->stopBuffering();
    yield colors("GOLD")."Plugin.yml Info".colors("DARK-GRAY").":";
    foreach($array as $k => $v)yield colors("PURPLE").$k.colors("DARK-GRAY").":".colors("").$v;
    yield colors("GRAY").str_repeat("--", 30);
}

function unPhar(string $label, array $args): Generator{
    $start = microtime(true);
    $config = file_exists(getcwd()."\Config.json") ? json_decode(file_get_contents(getcwd()."\Config.json"), true) : ["ignore-files" => [".idea", ".gitignore", "composer.json", "composer.lock", ".git", "composer.phar"], "show-path" => true, "input" => "", "output" => ""];
    if(!isset($args['name']) or ($name = $args['name']) === ""){
        yield colors("RED")."Usage: {$label} <file> [override: y|n]";
        return;
    }
    $result = ($config['output'] === "" ? $config['output'] : getcwd().DIRECTORY_SEPARATOR."Result").DIRECTORY_SEPARATOR.$name;
    $folderPath = ($config['input'] === "" ? $config['input'] : getcwd().DIRECTORY_SEPARATOR."Input").DIRECTORY_SEPARATOR.$name.".phar";

    //$folderPath = getcwd().DIRECTORY_SEPARATOR."Input".DIRECTORY_SEPARATOR.$name.".phar";
    //$result = getcwd().DIRECTORY_SEPARATOR."Result".DIRECTORY_SEPARATOR.$name;
    if(!file_exists($folderPath)){
        return yield "e>;$name.phar does not exist";
    }
    if(file_exists($result)){
        if(isset($args["override"]) && $args["override"] === "y"){
            yield 'Overwriting directory...';
            try{
                $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($result, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
                foreach($files as $file){
                    if($file->isFile()){
                        unlink($file->getRealPath());
        			}else{
		        		rmdir($file->getRealPath());
			        }
                }
                rmdir($result);
            }catch(\Throwable $e){
                 return yield "e>;Overwriting directory error has occurred: ".$e->getMessage();
            }
        }else{
            yield "e>;$name already exists";
            return;
        }
    }
    yield 'Adding files...';
    if(!is_dir($result))mkdir($result);
    $pharPath = "phar://".$folderPath;
    $files = 0;
    foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($pharPath)) as $fInfo){
        $path = $fInfo->getPathname();
		@mkdir(dirname($result.str_replace($pharPath, "", $path)), 0755, true);
		file_put_contents($result.str_replace($pharPath, "", $path), file_get_contents($path));
        if($config["show-path"])yield colors("GREEN").$name.str_replace($pharPath, "", $path);
        $files++;
	}
    yield colors("GRAY").str_repeat("--", 30);
    yield colors("LIGHT-PURPLE")."UnPhar success, {$files} files, done in ".round(microtime(true) - $start, 1)."s";
    yield colors("GRAY").str_repeat("--", 30);
}

function main(): Generator{
    yield "Starting PharCreator in version ".colors("DARK-AQUA")."v1.0.0";
    yield "Create and extract .phar quickly and easily using 1 command";
    load();
    yield 'For help, type "help" or "?"';
    for($i=0; $i < PHP_INT_MAX; $i++){
        $args = explode(" ", readline(""));
        $command = $args[0] ?? "";
        array_shift($args);
        switch($command){
            case "unphar":
                if(!isset($args[0])){
                    yield colors("RED")."Usage: {$command} <file> [override: y|n]";
                }else{
                    foreach(unPhar($command, ["name" => $args[0], "override" => (isset($args[1]) ? $args[1] : "n")]) as $line)yield $line;
                }
            break;
            case "phar":
            case "build":
            case "make":
                if(!isset($args[0])){
                    yield colors("RED")."Usage: {$command} <directory> [override: y|n]";
                }else{
                    foreach(BuilPhar($command, ["name" => $args[0], "override" => (isset($args[1]) ? $args[1] : "n")]) as $line)yield $line;
                }
            break;
            case "help":
            case "?":
                yield colors("GREEN")."phar <directory> [override: y|n]";
                yield colors("GREEN")."unphar <file> [override: y|n]";
                yield colors("GREEN")."exit";
            break;
            case "stop":
            case "exit":
                yield "Stopping PharCreator...";
                exit(0);
            break;
            default:
              yield colors("RED")."Unknown command: {$command}. Use help for a list of available commands.";
            break;
        }
    }
}

function load(){
    if(!file_exists(getcwd()."\Config.json"))file_put_contents(getcwd()."\Config.json", json_encode(["ignore-files" => [".idea", ".gitignore", "composer.json", "composer.lock", ".git", "composer.phar"], "show-path" => true, "input" => "", "output" => ""]));
    $config = file_exists(getcwd()."\Config.json") ? json_decode(file_get_contents(getcwd()."\Config.json"), true) : ["ignore-files" => [".idea", ".gitignore", "composer.json", "composer.lock", ".git", "composer.phar"], "show-path" => true, "input" => "", "output" => ""];
    $result = ($config['output'] !== "" ? $config['output'] : getcwd().DIRECTORY_SEPARATOR."Result");
    $input = ($config['input'] !== "" ? $config['input'] : getcwd().DIRECTORY_SEPARATOR."Input");
    if(getcwd().DIRECTORY_SEPARATOR."Result" === $result){
        if(!is_dir($result) && !mkdir($result)){
            error("An error occurred while creating {$result} directory");
            exit(1);
        }
    }
    if(getcwd().DIRECTORY_SEPARATOR."Input" === $input){
        if(!is_dir($input) && !mkdir($input)){
            error("An error occurred while creating {$input} directory");
            exit(1);
        }
    }
    foreach([$result, $input] as $path){
        if(!is_dir($path)){
            error("Folder {$path} not found");
            exit(1);
        }
    }
}

function readPluginYml(string $path): array {
    if(!file_exists($path = rtrim($path."/", DIRECTORY_SEPARATOR)."plugin.yml")){
        return ["error" => "missing-plugin.yml"];
    }
    $array = [];
    $data = explode("\n", file_get_contents($path, true));
    $value = fn(array $r): string => implode(":", $r);
    foreach($data as $k){
        $v = explode(":", $k);
        $key = $v[0];
        array_shift($v);
        $array[$key] = $value($v);
    }
    if(count($array) === 0){
        return ["error" => "plugin.yml-not-data"];
    }
    $matches = array_map("intval", explode(".", $array['version']));
    if(count($matches) < 2){
        return ["version" => $array["name"]." - ".$array["version"], "error" => "invalid-version"];
    }
    return $array;
}

foreach (main() as $line) {
    $msg = explode(">;", $line);
    switch($msg[0]){
        case "e":
            error($msg[1]);
        break;
        case "w":
            warn($msg[1]);
        break;
        default:
            info($msg[1] ?? $msg[0]);
        break;
    }
}