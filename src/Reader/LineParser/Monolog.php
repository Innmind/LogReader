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
    Maybe,
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

        $channel = $parts
            ->get('channel')
            ->map(static fn($channel) => $channel->toString())
            ->map(static fn($channel) => new Channel($channel));
        $level = $parts
            ->get('level')
            ->map(static fn($level) => $level->toString())
            ->map(static fn($level) => new Level($level));
        $message = $parts
            ->get('message')
            ->map(static fn($message) => $message->toString())
            ->map(static fn($message) => new Message($message));
        /** @var list<Attribute> */
        $attributes = Maybe::all($channel, $level, $message)
            ->map(static fn(Channel $channel, Level $level, Message $message) => [$channel, $level, $message])
            ->match(
                static fn($attributes) => $attributes,
                static fn() => throw new \RuntimeException,
            );

        $attributes = $parts
            ->get('context')
            ->map(static fn($context) => $context->toString())
            ->flatMap(static function($context): mixed {
                try {
                    return Maybe::just(Json::decode($context));
                } catch (Exception $e) {
                    return Maybe::nothing();
                }
            })
            ->map(static fn($context) => new Attribute\Attribute(
                'context',
                $context,
            ))
            ->match(
                static fn($context) => \array_merge($attributes, [$context]),
                static fn() => $attributes,
            );

        $attributes = $parts
            ->get('extra')
            ->map(static fn($extra) => $extra->toString())
            ->flatMap(static function($extra): mixed {
                try {
                    return Maybe::just(Json::decode($extra));
                } catch (Exception $e) {
                    return Maybe::nothing();
                }
            })
            ->map(static fn($extra) => new Attribute\Attribute(
                'extra',
                $extra,
            ))
            ->match(
                static fn($extra) => \array_merge($attributes, [$extra]),
                static fn() => $attributes,
            );

        return new Log(
            $parts
                ->get('time')
                ->map(static fn($time) => $time->toString())
                ->flatMap($this->clock->at(...))
                ->match(
                    static fn($time) => $time,
                    static fn() => throw new \RuntimeException,
                ),
            $line,
            ...$attributes,
        );
    }

    /**
     * @return Map<array-key, Str>
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
