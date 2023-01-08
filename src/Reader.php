<?php
declare(strict_types = 1);

namespace Innmind\LogReader;

use Innmind\LogReader\Reader\LineParser;
use Innmind\Filesystem\File\Content;
use Innmind\Immutable\Sequence;

/**
 * Return a stream of already parsed log lines
 */
final class Reader
{
    private LineParser $parse;

    public function __construct(LineParser $parser)
    {
        $this->parse = $parser;
    }

    public function __invoke(Content $file): Sequence
    {
        /** @var Sequence<Log> */
        return $file
            ->lines()
            ->map(static fn($line) => $line->str())
            ->filter(static fn($line) => !$line->empty())
            ->map($this->parse);
    }
}
