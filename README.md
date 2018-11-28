# Log reader

| `master` | `develop` |
|----------|-----------|
| [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Innmind/LogReader/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Innmind/LogReader/?branch=master) | [![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Innmind/LogReader/badges/quality-score.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/LogReader/?branch=develop) |
| [![Code Coverage](https://scrutinizer-ci.com/g/Innmind/LogReader/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Innmind/LogReader/?branch=master) | [![Code Coverage](https://scrutinizer-ci.com/g/Innmind/LogReader/badges/coverage.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/LogReader/?branch=develop) |
| [![Build Status](https://scrutinizer-ci.com/g/Innmind/LogReader/badges/build.png?b=master)](https://scrutinizer-ci.com/g/Innmind/LogReader/build-status/master) | [![Build Status](https://scrutinizer-ci.com/g/Innmind/LogReader/badges/build.png?b=develop)](https://scrutinizer-ci.com/g/Innmind/LogReader/build-status/develop) |

Allow you to parse symfony and apache access logs.

**BEWARE, it can take a lot of time depending on the amount of data** (For a typical symfony `dev.log` it starts to really slow down after 10k lines)

## Installation

```sh
composer require innmind/log-reader
```

## Usage

```php
use Innmind\LogReader\{
    Reader/Synchronous,
    Reader/LineParser/Symfony,
    Log
};
use Innmind\TimeContinuum\TimeContinuum\Earth;
use Innmind\Filesystem\Adapter\FilesystemAdapter;
use Psr\Log\LogLevel;

$read = new Synchronous(
    new Symfony(new Earth)
);
$fs = new FilesystemAdapter('var/logs');
$read($fs->get('prod.log'))
    ->filter(static function(Log $log): bool {
        return $log->attributes()->get('level')->value() === LogLevel::CRITICAL;
    })
    ->foreach(static function(Log $log): void {
        echo $log->attributes()->get('message')->value();
    });
```

The above example will print all messages that were logged at a critical level.
