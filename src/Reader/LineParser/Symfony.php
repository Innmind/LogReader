<?php
declare(strict_types = 1);

namespace Innmind\LogReader\Reader\LineParser;

use Innmind\LogReader\{
    Reader\LineParser,
    Log,
    Log\Attribute,
    Log\Attribute\Symfony\Channel,
    Log\Attribute\Symfony\Level,
    Log\Attribute\Symfony\Message
};
use Innmind\TimeContinuum\TimeContinuumInterface;
use Innmind\Immutable\{
    Str,
    Map
};

final class Symfony implements LineParser
{
    private const FORMAT = '~^\[(?P<time>\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (?P<channel>[a-zA-Z-_]+)\.(?P<level>EMERGENCY|ALERT|CRITICAL|ERROR|WARNING|NOTICE|INFO|DEBUG): (?P<message>.+) (?P<context>[\{\[].*[\]\}]) (?P<extra>[\{\[].*[\]\}])$~';

    private $clock;
    private $format;

    public function __construct(
        TimeContinuumInterface $clock,
        string $format = null
    ) {
        $this->clock = $clock;
        $this->format = $format ?? self::FORMAT;
    }

    public function __invoke(Str $line): Log
    {
        $parts = $line->capture($this->format);

        return new Log(
            $this->clock->at((string) $parts->get('time')),
            $line,
            (new Map('string', Attribute::class))
                ->put('channel', new Channel((string) $parts->get('channel')))
                ->put('level', new Level((string) $parts->get('level')))
                ->put('message', new Message((string) $parts->get('message')))
                ->put(
                    'context',
                    new Attribute\Attribute(
                        'context',
                        json_decode((string) $parts->get('context'), true)
                    )
                )
                ->put(
                    'extra',
                    new Attribute\Attribute(
                        'extra',
                        json_decode((string) $parts->get('extra'), true)
                    )
                )
        );
    }
}
