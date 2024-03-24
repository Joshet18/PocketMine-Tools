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
use Tools\Terminal;
class Query extends CommandBase {

    public function execute(ConsoleSender $sender, string $label, array $args){
        $port = ((isset($args[1]) && is_numeric($args[1])) ? (int)$args[1] : 19132);
        if(!isset($args[0])){
            $sender->sendMessage(Terminal::RED."Usage: /{$label}".Terminal::GOLD." <ip> [port]");
            return;
        }
        $result = $this->sendQuery($args[0], $port);
        if(is_null($result)){
            $sender->sendMessage(Terminal::RED."El servidor ".Terminal::GOLD."{$args[0]}:{$port} ".Terminal::RED."no ha mandado ningun dato!");
            return;
        }
        $sender->sendMessage(Terminal::LIGHT_PURPLE."Hostname".Terminal::GRAY.": ".Terminal::WHITE.$result[1]);
        $sender->sendMessage(Terminal::LIGHT_PURPLE."GameType".Terminal::GRAY.": ".Terminal::WHITE.$result[3]);
        $sender->sendMessage(Terminal::LIGHT_PURPLE."GameId".Terminal::GRAY.": ".Terminal::WHITE.$result[5]);
        $sender->sendMessage(Terminal::LIGHT_PURPLE."Version".Terminal::GRAY.": ".Terminal::WHITE.$result[7]);
        $sender->sendMessage(Terminal::LIGHT_PURPLE."Server Engine".Terminal::GRAY.": ".Terminal::WHITE.$result[9]);
        $sender->sendMessage(Terminal::LIGHT_PURPLE."Plugins".Terminal::GRAY.": ".Terminal::WHITE.$result[11]);
        $sender->sendMessage(Terminal::LIGHT_PURPLE."Map".Terminal::GRAY.": ".Terminal::WHITE.$result[13]);
        $sender->sendMessage(Terminal::LIGHT_PURPLE."Online".Terminal::GRAY.": ".Terminal::WHITE.$result[15].Terminal::GRAY."/".Terminal::WHITE.$result[17]);
        $sender->sendMessage(Terminal::LIGHT_PURPLE."Whitelist".Terminal::GRAY.": ".Terminal::WHITE.$result[19]);
        $sender->sendMessage(Terminal::LIGHT_PURPLE."Host IP".Terminal::GRAY.": ".Terminal::WHITE.$result[21].Terminal::GRAY." (".($args[0]).")");
        $sender->sendMessage(Terminal::LIGHT_PURPLE."Host Port".Terminal::GRAY.": ".Terminal::WHITE.$result[23].Terminal::GRAY." (".$port.")");
        for($i = 0; $i !== 27; $i++)unset($result[$i]);
        $sender->sendMessage(Terminal::LIGHT_PURPLE."Players".Terminal::GRAY.": ".Terminal::WHITE.implode(", ", $result));
    }

    private function sendQuery(string $host, int $port = 19132):?array{
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
}