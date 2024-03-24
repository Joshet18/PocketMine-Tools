<?php

define('MAIN_PATH', __DIR__);

require MAIN_PATH."/Main.php";
require MAIN_PATH."/MainLogger.php";
require MAIN_PATH."/Terminal.php";

require MAIN_PATH."/utils/Utils.php";
require MAIN_PATH."/utils/Config.php";

require MAIN_PATH."/commands/CommandBase.php";
require MAIN_PATH."/commands/ConsoleSender.php";
require MAIN_PATH."/commands/CommandManager.php";

require MAIN_PATH."/commands/defaults/Stop.php";
require MAIN_PATH."/commands/defaults/Help.php";
require MAIN_PATH."/commands/defaults/Query.php";
require MAIN_PATH."/commands/defaults/UnPhar.php";
require MAIN_PATH."/commands/defaults/MakePhar.php";