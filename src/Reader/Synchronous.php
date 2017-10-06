<?php
declare(strict_types = 1);

namespace Innmind\LogReader\Reader;

use Innmind\LogReader\{
    Reader,
    Log
};
use Innmind\Filesystem\File;
use Innmind\Immutable\{
    StreamInterface,
    Stream,
    Str
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
    public function parse(File $file): StreamInterface
    {
        $content = $file->content();
        $line = new Str('');
        $stream = new Stream(Log::class);

        while (!$content->end()) {
            $line = $line->append((string) $content->read(8192));
            $splits = $line->split("\n");

            if ($splits->size() > 2) {
                $line = $splits->last();
                $lines = $splits->dropEnd(1);

                foreach ($lines as $line) {
                    $stream = $stream->add(($this->parse)($line));
                }
            }
        }

        return $stream;
    }
}
