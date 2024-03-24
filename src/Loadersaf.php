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
    echo colors("GOLD")."[".colors("DARK-AQUA").gmdate("H:i:s", time()).colors("GOLD")."] [".colors("AQUA")."PocketMineTools/INFO".colors("GOLD")."] ".colors("").": ".$message.colors("").PHP_EOL;
}
function warn(string $message){
    echo colors("GOLD")."[".colors("DARK-AQUA").gmdate("H:i:s", time()).colors("GOLD")."] [".colors("ORANGE")."PocketMineTools/WARN".colors("GOLD")."] ".colors("YELLOW").": ".$message.colors("").PHP_EOL;
}
function error(string $message){
    echo colors("GOLD")."[".colors("DARK-AQUA").gmdate("H:i:s", time()).colors("GOLD")."] [".colors("DARK-RED")."PocketMineTools/ERROR".colors("GOLD")."] ".colors("RED").": ".$message.colors("").PHP_EOL;
}

function BuilPhar(string $label, array $opts): Generator {
    $start = microtime(true);
    $config = file_exists(getcwd()."\Config.json") ? json_decode(file_get_contents(getcwd()."\Config.json"), true) : ["ignore-files" => [], "show-path" => true, "input" => "", "output" => ""];
    if(!isset($opts['name']) or ($name = $opts['name']) === ""){
        yield colors("RED")."Usage: {$label} <directory> [override: y|n]";
        return;
    }
    $result = ($config['output'] !== "" ? $config['output'] : getcwd().DIRECTORY_SEPARATOR."Result");
    $result .= DIRECTORY_SEPARATOR;
    $input = ($config['input'] !== "" ? $config['input'] : getcwd().DIRECTORY_SEPARATOR."Input");
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
    foreach($phar as $file => $finfo){
        /** @var \PharFileInfo $finfo */
        if($finfo->getSize() > (1024 * 512)){
            yield "Compressing " . $finfo->getFilename();
            $finfo->compress(Phar::GZ);
        }
    }
    $phar->compressFiles(Phar::GZ);
    $phar->stopBuffering();
    yield colors("GOLD")."Plugin.yml Info".colors("DARK-GRAY").":";
    foreach($array as $k => $v)yield colors("PURPLE").$k.colors("DARK-GRAY").":".colors("").$v;
    yield colors("GRAY").str_repeat("--", 30);
}

function unPhar(string $label, array $args): Generator{
    $start = microtime(true);
    $config = file_exists(getcwd()."\Config.json") ? json_decode(file_get_contents(getcwd()."\Config.json"), true) : ["ignore-files" => [], "show-path" => true, "input" => "", "output" => ""];
    if(!isset($args['name']) or ($name = $args['name']) === ""){
        yield colors("RED")."Usage: {$label} <file> [override: y|n]";
        return;
    }
    $result = ($config['output'] !== "" ? $config['output'] : getcwd().DIRECTORY_SEPARATOR."Result").DIRECTORY_SEPARATOR.$name;
    $folderPath = ($config['input'] !== "" ? $config['input'] : getcwd().DIRECTORY_SEPARATOR."Input").DIRECTORY_SEPARATOR.$name.".phar";
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

function sendQuery(string $host, int $port = 19132):?array{
    $socket = @fsockopen("udp://".$host, $port);
    if(!$socket)return null;
    stream_set_timeout($socket, 1);
    $online = @fwrite($socket, "\xFE\xFD\x09\x10\x20\x30\x40\xFF\xFF\xFF\x01");
    if(!$online) return null;
    $challenge = @fread($socket, 1400);
    $res = stream_get_meta_data($socket);
    if($res['timed_out']) return null;
    if(!$challenge) return null;
    $challenge = substr(preg_replace("/[^0-9-]/si", "", $challenge), 1);
    $query = sprintf("\xFE\xFD\x00\x10\x20\x30\x40%c%c%c%c\xFF\xFF\xFF\x01", $challenge >> 24, $challenge >> 16, $challenge >> 8, $challenge >> 0);
    if(!@fwrite($socket, $query))return null;
    $response = [];
    $response[] = @fread($socket, 2048);
    $response = implode($response);
    $response = substr($response, 16);
    $response = explode("\0", $response);
    array_pop($response);
    array_pop($response);
    @fclose($socket);
    return $response;
}

function showServerQuery(string $host, int $port = 19132){
    $result = sendQuery($host, $port);
    if(is_null($result)){
        yield "e>;El servidor ".colors("GOLD")."{$host}:{$port} ".colors("RED")."no ha mandado ningun dato!";
    }else{
        yield colors("LIGHT-PURPLE")."Hostname".colors("GRAY").": ".colors("WHITE").$result[1];
        yield colors("LIGHT-PURPLE")."GameType".colors("GRAY").": ".colors("WHITE").$result[3];
        yield colors("LIGHT-PURPLE")."GameId".colors("GRAY").": ".colors("WHITE").$result[5];
        yield colors("LIGHT-PURPLE")."Version".colors("GRAY").": ".colors("WHITE").$result[7];
        yield colors("LIGHT-PURPLE")."Server Engine".colors("GRAY").": ".colors("WHITE").$result[9];
        yield colors("LIGHT-PURPLE")."Plugins".colors("GRAY").": ".colors("WHITE").$result[11];
        yield colors("LIGHT-PURPLE")."Map".colors("GRAY").": ".colors("WHITE").$result[13];
        yield colors("LIGHT-PURPLE")."Online".colors("GRAY").": ".colors("WHITE").$result[15].colors("GRAY")."/".colors("WHITE").$result[17];
        yield colors("LIGHT-PURPLE")."Whitelist".colors("GRAY").": ".colors("WHITE").$result[19];
        yield colors("LIGHT-PURPLE")."Host IP".colors("GRAY").": ".colors("WHITE").$result[21].colors("GRAY")." (".($host).")";
        yield colors("LIGHT-PURPLE")."Host Port".colors("GRAY").": ".colors("WHITE").$result[23].colors("GRAY")." (".$port.")";
        for($i = 0; $i !== 27; $i++)unset($result[$i]);
        yield colors("LIGHT-PURPLE")."Players".colors("GRAY").": ".colors("WHITE").implode(", ", $result);
    }
}

function main(): Generator{
    yield "Starting PocketMineTools in version ".colors("DARK-AQUA")."v1.0.1";
    yield "Create, extract encode and decode plugins quickly and easily using 1 command";
    load();
    yield 'For help, type "help" or "?"';
    foreach(ConsoleSender() as $ouput)yield $ouput;
}

function ConsoleSender(): Generator{
    for($i=PHP_INT_MIN; $i < PHP_INT_MAX; $i++){
        $args = explode(" ", readline(""));
        $command = $args[0];
        array_shift($args);
        if($command !== "")switch($command){
            case "skeleton":
            break;
            case "query":
                if(!isset($args[0]) or $args[0] === ""){
                    yield colors("RED")."Usage: query <ip> [port]";
                }else{
                    $port = 19132;
                    if(isset($args[1]) && is_numeric($args[1]))$port = (int)$args[1];
                    foreach(showServerQuery($args[0], $port) as $msg)yield $msg;
                }
            break;
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
                yield colors("GREEN")."query <ip> [port]";
                yield colors("GREEN")."exit";
            break;
            case "stop":
            case "exit":
                yield "Stopping PocketMineTools...";
                exit(0);
            break;
            default:
              yield colors("RED")."Unknown command: {$command}. Use help for a list of available commands.";
            break;
        }
    }
}

function load(){
    if(!file_exists(getcwd()."\Config.json"))file_put_contents(getcwd()."\Config.json", json_encode(["ignore-files" => [], "show-path" => true, "input" => "", "output" => ""]));
    $config = file_exists(getcwd()."\Config.json") ? json_decode(file_get_contents(getcwd()."\Config.json"), true) : ["ignore-files" => [], "show-path" => true, "input" => "", "output" => ""];
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