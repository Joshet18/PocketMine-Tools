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
use Tools\Terminal;
final class MainLogger {
    const LOG_LEVEL_DEBUG = 0;
    const LOG_LEVEL_INFO = 1;
    const LOG_LEVEL_NOTICE = 2;
    const LOG_LEVEL_WARNING = 3;
    const LOG_LEVEL_ERROR = 4;
    const LOG_LEVEL_EMERGENCY = 5;
    const LOG_LEVEL_ALERT = 6;
    const LOG_LEVEL_CRITICAL = 7;
	
	private static $instance = null;
    private string $format = Terminal::GOLD."[".Terminal::DARK_AQUA."%s".Terminal::GOLD."] ".Terminal::RESET."%s[%s/%s]: %s".Terminal::RESET;
    private bool $logDebug = false;
    private \DateTimeZone $DateTimeZone;
    
    public function __construct(){
        if(self::$instance !== null)throw new \LogicException("Only one mainlogger instance can exist at once");
        self::$instance = $this;
        $tz = ini_get('date.timezone');
		if($tz === false)throw new \Exception('date.timezone INI entry should always exist');
        $this->DateTimeZone = new \DateTimeZone($tz);
    }

    public function emergency(string $message){
		$this->send($message, self::LOG_LEVEL_EMERGENCY, "EMERGENCY", Terminal::RED);
	}

	public function alert(string $message){
		$this->send($message, self::LOG_LEVEL_ALERT, "ALERT", Terminal::RED);
	}

	public function critical(string $message){
		$this->send($message, self::LOG_LEVEL_CRITICAL, "CRITICAL", Terminal::RED);
	}

	public function error(string $message){
		$this->send($message, self::LOG_LEVEL_ERROR, "ERROR", Terminal::DARK_RED);
	}

	public function warning(string $message){
		$this->send($message, self::LOG_LEVEL_WARNING, "WARNING", Terminal::YELLOW);
	}

	public function notice(string $message){
		$this->send($message, self::LOG_LEVEL_NOTICE, "NOTICE", Terminal::AQUA);
	}

	public function info(string $message){
		$this->send($message, self::LOG_LEVEL_INFO, "INFO", Terminal::WHITE);
	}

	public function debug($message, bool $force = false){
		if(!$this->logDebug && !$force)return;
		$this->send($message, self::LOG_LEVEL_DEBUG, "DEBUG", Terminal::GRAY);
	}

	public function setLogDebug(bool $logDebug) : void{
		$this->logDebug = $logDebug;
	}

    private function send($message, int $level, string $prefix, $color):void{
        $time = new \DateTime('now', $this->DateTimeZone);
        $message = sprintf($this->format, $time->format("H:i:s.v"), $color, 'PocketmineTools', $prefix, "{$color}{$message}");
        echo $message.PHP_EOL;
    }
}