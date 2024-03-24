<?php

require_once __DIR__."/autoload.php";

use Tools\Main;
use Tools\Utils\Utils;

Main::create(Utils::assumeNotFalse(realpath(Utils::assumeNotFalse(getcwd()))));