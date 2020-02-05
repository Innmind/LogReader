# Log reader

[![Build Status](https://github.com/Innmind/LogReader/workflows/CI/badge.svg)](https://github.com/Innmind/LogReader/actions?query=workflow%3ACI)
[![codecov](https://codecov.io/gh/Innmind/LogReader/branch/develop/graph/badge.svg)](https://codecov.io/gh/Innmind/LogReader)
[![Type Coverage](https://shepherd.dev/github/Innmind/LogReader/coverage.svg)](https://shepherd.dev/github/Innmind/LogReader)

Allow you to parse symfony and apache access logs.

**BEWARE, it can take a lot of time depending on the amount of data** (For a typical symfony `dev.log` it starts to really slow down after 10k lines)

## Installation

```sh
composer require innmind/log-reader
```

## Usage

```php
use Innmind\LogReader\{
    Reader\Synchronous,
    Reader\LineParser\Monolog,
    Log
};
use Innmind\TimeContinuum\TimeContinuum\Earth;
use Innmind\Filesystem\Adapter\FilesystemAdapter;
use Psr\Log\LogLevel;

$read = new Synchronous(
    new Monolog(new Earth)
);
$fs = new FilesystemAdapter('var/logs');
$read($fs->get('prod.log')->content())
    ->filter(static function(Log $log): bool {
        return $log->attributes()->get('level')->value() === LogLevel::CRITICAL;
    })
    ->foreach(static function(Log $log): void {
        echo $log->attributes()->get('message')->value();
    });
```

The above example will print all messages that were logged at a critical level.

**Note**: if parsing the `context` or `extra` attributes of a monolog line they won't be exposed as attributes in the `Log` object. This behaviour is implemented to not make the whole parsing fail due to this error.
