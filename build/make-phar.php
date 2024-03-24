<?php

function preg_quote_array(array $strings, string $delim) : array{
	return array_map(function(string $str) use ($delim) : string{ return preg_quote($str, $delim); }, $strings);
}

function buildPhar(string $pharPath, string $basePath, array $includedPaths, array $metadata, string $stub, int $signatureAlgo = \Phar::SHA1, ?int $compression = null){
	$basePath = rtrim(str_replace("/", DIRECTORY_SEPARATOR, $basePath), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
	$includedPaths = array_map(function(string $path) : string{
		return rtrim(str_replace("/", DIRECTORY_SEPARATOR, $path), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
	}, $includedPaths);
	yield "Creating output file $pharPath";
	if(file_exists($pharPath)){
		yield "Phar file already exists, overwriting...";
		try{
			\Phar::unlinkArchive($pharPath);
		}catch(\PharException $e){
			//unlinkArchive() doesn't like dodgy phars
			unlink($pharPath);
		}
	}

	yield "Adding files...";

	$start = microtime(true);
	$phar = new \Phar($pharPath);
	$phar->setMetadata($metadata);
	$phar->setStub($stub);
	$phar->setSignatureAlgorithm($signatureAlgo);
	$phar->startBuffering();
	$excludedSubstrings = preg_quote_array([
		realpath($pharPath),
	], '/');

	$folderPatterns = preg_quote_array([
		DIRECTORY_SEPARATOR . 'tests' . DIRECTORY_SEPARATOR,
		DIRECTORY_SEPARATOR . '.'
	], '/');

	$basePattern = preg_quote(rtrim($basePath, DIRECTORY_SEPARATOR), '/');
	foreach($folderPatterns as $p){
		$excludedSubstrings[] = $basePattern . '.*' . $p;
	}

	$regex = sprintf('/^(?!.*(%s))^%s(%s).*/i',
		 implode('|', $excludedSubstrings), //String may not contain any of these substrings
		 preg_quote($basePath, '/'), //String must start with this path...
		 implode('|', preg_quote_array($includedPaths, '/')) //... and must be followed by one of these relative paths, if any were specified. If none, this will produce a null capturing group which will allow anything.
	);

	$directory = new \RecursiveDirectoryIterator($basePath, \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::FOLLOW_SYMLINKS | \FilesystemIterator::CURRENT_AS_PATHNAME); //can't use fileinfo because of symlinks
	$iterator = new \RecursiveIteratorIterator($directory);
	$regexIterator = new \RegexIterator($iterator, $regex);

	$count = count($phar->buildFromIterator($regexIterator, $basePath));
	yield "Added $count files";

	if($compression !== null){
		yield "Compressing files...";
		$phar->compressFiles($compression);
		yield "Finished compression";
	}
	$phar->stopBuffering();

	yield "Done in " . round(microtime(true) - $start, 3) . "s";
}

function main() : void{
	if(ini_get("phar.readonly") == 1){
		echo "Set phar.readonly to 0 with -dphar.readonly=0" . PHP_EOL;
		exit(1);
	}

	$pharPath = getcwd() . DIRECTORY_SEPARATOR . "PocketMine-Tools.phar";
	foreach(buildPhar(
		$pharPath,
		dirname(__DIR__) . DIRECTORY_SEPARATOR,
		['src'],
		[],
		<<<'STUB'
<?php

$tmpDir = sys_get_temp_dir();
if(!is_readable($tmpDir) or !is_writable($tmpDir)){
	echo "ERROR: tmpdir $tmpDir is not accessible." . PHP_EOL;
	echo "Check that the directory exists, and that the current user has read/write permissions for it." . PHP_EOL;
	echo "Alternatively, set 'sys_temp_dir' to a different directory in your php.ini file." . PHP_EOL;
	exit(1);
}

require("phar://" . __FILE__ . "/src/Run.php");
__HALT_COMPILER();
STUB
,
		\Phar::SHA1,
		\Phar::GZ
	) as $line){
		echo $line . PHP_EOL;
	}
}

main();