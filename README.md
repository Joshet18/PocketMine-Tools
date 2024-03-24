## Commands:
* `stop`: stop pocketmine-tools
* `help`: get list of all commands.
* `query <ip> [port]`: get server status
* `unphar <phar> [override: y|n]`: Extract .phar file
* `phar <directory> [override: y|n]`: Make .phar file
## Build Phar for SourceCode
* 1. Download the source code from https://github.com/Joshet18/PocketMine-Tools
* 2. Extract Zip file
* 3. Open terminal and go to directory where you extracted zip file
* 4. Run `php -d phar.readonly=0 build/make-phar.php`
# Windows:
```
start.cmd
```
# Others System:
```
php -d phar.readonly=0 PocketMine-Tools.phar
```