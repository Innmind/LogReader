<?php
declare(strict_types = 1);

namespace Innmind\LogReader\Reader\LineParser;

use Innmind\LogReader\{
    Reader\LineParser,
    Log,
    Log\Attribute,
    Log\Attribute\Monolog\Channel,
    Log\Attribute\Monolog\Level,
    Log\Attribute\Monolog\Message
};
use Innmind\TimeContinuum\TimeContinuumInterface;
use Innmind\Json\Json;
use Innmind\Immutable\{
    Str,
    Map,
    MapInterface
};

final class Monolog implements LineParser
{
    private const FORMAT = '~^\[(?P<time>\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (?P<channel>[a-zA-Z-_]+)\.(?P<level>EMERGENCY|ALERT|CRITICAL|ERROR|WARNING|NOTICE|INFO|DEBUG): (?P<message>.+) (?P<context>[\{\[].*[\]\}]) (?P<extra>[\{\[].*[\]\}])$~';
    private const FORMAT_WITHOUT_EXTRA = '~^\[(?P<time>\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (?P<channel>[a-zA-Z-_]+)\.(?P<level>EMERGENCY|ALERT|CRITICAL|ERROR|WARNING|NOTICE|INFO|DEBUG): (?P<message>.+) (?P<context>[\{\[].*[\]\}])$~';

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
        $parts = $this->decode($line);

        return new Log(
            $this->clock->at((string) $parts->get('time')),
            $line,
            Map::of('string', Attribute::class)
                ('channel', new Channel((string) $parts->get('channel')))
                ('level', new Level((string) $parts->get('level')))
                ('message', new Message((string) $parts->get('message')))
                (
                    'context',
                    new Attribute\Attribute(
                        'context',
                        Json::decode((string) $parts->get('context'))
                    )
                )
                (
                    'extra',
                    new Attribute\Attribute(
                        'extra',
                        Json::decode((string) $parts->get('extra')->trim())
                    )
                )
        );
    }

    private function decode(Str $line): MapInterface
    {
        $parts = $line->capture($this->format);

        if ($parts->contains('time')) {
            return $parts;
        }

        return $line
            ->capture(self::FORMAT_WITHOUT_EXTRA)
            ->put('extra', new Str('[]'));
    }
}
