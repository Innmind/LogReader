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

    /**
     * {@inheritdoc}
     */
    public function __invoke(Readable $file): Sequence
    {
        return Sequence::lazy(
            Log::class,
            function() use ($file): \Generator {
                $file->rewind();

                while (!$file->end()) {
                    $line = $file->readLine();

                    if ($line->empty()) {
                        continue;
                    }

                    yield ($this->parse)($line);
                }
            },
        );
    }
}
