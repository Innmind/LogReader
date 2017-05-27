<?php
declare(strict_types = 1);

namespace Innmind\LogReader\Reader;

use Innmind\LogReader\{
    Reader,
    Log,
    Log\Attribute,
    Log\Attribute\Symfony\Channel,
    Log\Attribute\Symfony\Level,
    Log\Attribute\Symfony\Message
};
use Innmind\TimeContinuum\TimeContinuumInterface;
use Innmind\Filesystem\FileInterface;
use Innmind\Immutable\{
    StreamInterface,
    Stream,
    Map,
    Str
};

final class Symfony implements Reader
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

    /**
     * {@inheritdoc}
     */
    public function parse(FileInterface $file): StreamInterface
    {
        $content = $file->content();
        $stream = new Stream(Log::class);
        $line = new Str('');

        while (!$content->isEof()) {
            $line = $line->append($content->read(8192));

            $splits = $line->split("\n");

            if ($splits->size() > 2) {
                $line = $splits->last();
                $stream = $splits
                    ->dropEnd(1)
                    ->filter(function(Str $line): bool {
                        return $line->matches($this->format);
                    })
                    ->reduce(
                        $stream,
                        function(Stream $stream, Str $line): Stream {
                            return $stream->add($this->read($line));
                        }
                    );
            }
        }

        return $stream;
    }

    private function read(Str $line): Log
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
