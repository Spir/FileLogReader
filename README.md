FileLogReader
========

Psr 3 daily file log reader

[![Build Status](https://travis-ci.org/Spir/FileLogReader.png?branch=master)](https://travis-ci.org/Spir/FileLogReader)

This lib assume that your log are similar to this format:
```
	[YYYY-MM-DD HH:MM:SS] ENVIRONMENT.LEVEL: DESCRIPTION
	STACK
```


Quick example
----

```php
$fileLogReader = new FileLogReader('/path/to/your/logs');
$sapi = php_sapi_name();
$date = new DateTime();

$logFiles = $fileLogReader->getAll($sapi);
var_dump($logFiles);

$todayLogs = $fileLogReader->getFile($sapi, $date);
var_dump($todayLogs);
```
