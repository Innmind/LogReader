<?php
declare(strict_types = 1);

namespace Innmind\LogReader\Reader;

use Innmind\LogReader\{
    Reader,
    Log,
};
use Innmind\Stream\Readable;
use Innmind\Immutable\Sequence;

/**
 * Return a stream of already parsed log lines
 */
final class Synchronous implements Reader
{
    private LineParser $parse;

    public function __construct(LineParser $parser)
    {
        $this->parse = $parser;
    }

    public function __invoke(Readable $file): Sequence
    {
        $file->rewind();
        /** @var Sequence<Log> */
        $lines = Sequence::of(Log::class);

        while (!$file->end()) {
            $line = $file->readLine();

            if ($line->empty()) {
                continue;
            }

            $lines = ($lines)(($this->parse)($line));
        }

        return $lines;
    }
}
