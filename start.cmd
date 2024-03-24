@echo off
TITLE PharCreator
cd /d %~dp0
set PHP_BINARY=
where /q php.exe
if %ERRORLEVEL%==0 (
	set PHP_BINARY=php
)
if exist php\php.exe (
	set PHP_BINARY=php\php.exe
)
if "%PHP_BINARY%"=="" (
	echo Couldn't find a PHP binary in system PATH or "%~dp0php"
	pause
	exit 1
)
%PHP_BINARY% -d phar.readonly=0 PocketMine-Tools.phar