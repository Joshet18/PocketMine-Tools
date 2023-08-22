## Commands:
* `phar <directory> [override: y|n]`: Creates a Phar archive for its distribution 
* `unphar <file> [override: y|n]`: Extracts the source from its Phar file
* `exit`: Stop PharConvertor
# Windows:
```
start.cmd
```
# Others System:
```
php -d phar.readonly=0 PharCreator.php
```