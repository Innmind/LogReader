# Log reader

[![Build Status](https://github.com/Innmind/LogReader/workflows/CI/badge.svg?branch=master)](https://github.com/Innmind/LogReader/actions?query=workflow%3ACI)
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
    Reader,
    LineParser\Monolog,
    Log,
};
use Innmind\OperatingSystem\Factory;
use Innmind\Filesystem\Name;
use Innmind\Url\Path;
use Psr\Log\LogLevel;

$os = Factory::build();

$read = Reader::of(
    Monolog::of($os->clock()),
);
$os
    ->filesystem()
    ->mount(Path::of('var/logs/'))
    ->get(Name::of('prod.log'))
    ->map(static fn($file) => $file->content())
    ->map($read)
    ->map(
        static fn($logs) => $logs
            ->filter(
                static fn($log) => $log
                    ->attribute('level')
                    ->filter(static fn($level) => $level->value() === LogLevel::CRITICAL)
                    ->match(
                        static fn() => true,
                        static fn() => false,
                    ),
            )
            ->foreach(
                static fn($log) => $log
                    ->attribute('message')
                    ->match(
                        static fn($attribute) => print($attribute->value()),
                        static fn() => print('No message found'),
                    ),
            ),
        static fn() => print('File does not exist'),
    );
```

The above example will print all messages that were logged at a critical level.

**Note**: if parsing the `context` or `extra` attributes of a monolog line fail they won't be exposed as attributes in the `Log` object. This behaviour is implemented to not make the whole parsing fail due to this error.

**Note 2**: if a line can't be parsed for some reason it will simply be ignored and not exposed. Again this behaviour is implemented to not make the whole parsing fail.
