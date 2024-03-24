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
namespace Tools\Utils;
class Utils {
	public static function assumeNotFalse(mixed $value, \Closure|string $context = "This should never be false") : mixed{
		if($value === false){
			throw new \Exception("Assumption failure: " . (is_string($context) ? $context : $context()) . " (THIS IS A BUG)");
		}
		return $value;
	}

    public static function stringifyKeys(array $array) : \Generator{
		foreach($array as $key => $value){ // @phpstan-ignore-line - this is where we fix the stupid bullshit with array keys :)
			yield (string) $key => $value;
		}
	}
}