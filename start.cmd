@echo off
TITLE PharCreator
cd /d %~dp0
set PHP_BINARY=
where /q php.exe
if %ERRORLEVEL%==0 (
	set PHP_BINARY=php
)
if exist windows-php\php.exe (
	set PHP_BINARY=windows-php\php.exe
)
if "%PHP_BINARY%"=="" (
	echo Couldn't find a PHP binary in system PATH or "%~dp0windows-php"
	pause
	exit 1
)
%PHP_BINARY% -d phar.readonly=0 PharCreator.php