<?php
declare(strict_types = 1);

namespace Innmind\LogReader\Reader;

use Innmind\LogReader\{
    Reader,
    Log,
};
use Innmind\Filesystem\File\Content;
use Innmind\Immutable\Sequence;

/**
 * Use a generator that will parse the file only when you'll manipulate the
 * stream returned
 */
final class OnDemand implements Reader
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
