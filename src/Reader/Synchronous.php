<?php
declare(strict_types = 1);

namespace Innmind\LogReader\Reader;

use Innmind\LogReader\{
    Reader,
    Log,
};
use Innmind\Stream\Readable;
use Innmind\Immutable\{
    StreamInterface,
    Stream,
};

/**
 * Return a stream of already parsed log lines
 */
final class Synchronous implements Reader
{
    private $parse;

    public function __construct(LineParser $parser)
    {
        $this->parse = $parser;
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(Readable $file): StreamInterface
    {
        $file->rewind();
        $stream = new Stream(Log::class);

        while (!$file->end()) {
            $line = $file->readLine();

            if ($line->length() === 0) {
                continue;
            }

            $stream = $stream->add(($this->parse)($line));
        }

        return $stream;
    }
}
