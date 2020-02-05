<?php
declare(strict_types = 1);

namespace Innmind\LogReader\Reader\LineParser;

use Innmind\LogReader\{
    Reader\LineParser,
    Log,
    Log\Attribute,
    Log\Attribute\Monolog\Channel,
    Log\Attribute\Monolog\Level,
    Log\Attribute\Monolog\Message,
};
use Innmind\TimeContinuum\Clock;
use Innmind\Json\{
    Json,
    Exception\Exception,
};
use Innmind\Immutable\{
    Str,
    Map,
};

final class Monolog implements LineParser
{
    private const FORMAT = '~^\[(?P<time>\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (?P<channel>[a-zA-Z-_]+)\.(?P<level>EMERGENCY|ALERT|CRITICAL|ERROR|WARNING|NOTICE|INFO|DEBUG): (?P<message>.+) (?P<context>[\{\[].*[\]\}]) (?P<extra>[\{\[].*[\]\}])$~';
    private const FORMAT_WITHOUT_EXTRA = '~^\[(?P<time>\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (?P<channel>[a-zA-Z-_]+)\.(?P<level>EMERGENCY|ALERT|CRITICAL|ERROR|WARNING|NOTICE|INFO|DEBUG): (?P<message>.+) (?P<context>[\{\[].*[\]\}])$~';

    private Clock $clock;
    private string $format;

    public function __construct(Clock $clock, string $format = null)
    {
        $this->clock = $clock;
        $this->format = $format ?? self::FORMAT;
    }

    public function __invoke(Str $line): Log
    {
        $parts = $this->decode($line);

        $attributes = [
            new Channel($parts->get('channel')->toString()),
            new Level($parts->get('level')->toString()),
            new Message($parts->get('message')->toString()),
        ];

        try {
            $attributes[] = new Attribute\Attribute(
                'context',
                Json::decode($parts->get('context')->toString()),
            );
        } catch (Exception $e) {
            // do nothing
        }

        try {
            $attributes[] = new Attribute\Attribute(
                'extra',
                Json::decode($parts->get('extra')->trim()->toString()),
            );
        } catch (Exception $e) {
            // do nothing
        }

        return new Log(
            $this->clock->at($parts->get('time')->toString()),
            $line,
            ...$attributes,
        );
    }

    /**
     * @return Map<scalar, Str>
     */
    private function decode(Str $line): Map
    {
        $parts = $line->capture($this->format);

        if ($parts->contains('time')) {
            return $parts;
        }

        return $line
            ->capture(self::FORMAT_WITHOUT_EXTRA)
            ->put('extra', Str::of('[]'));
    }
}
