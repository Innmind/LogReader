<?php
declare(strict_types = 1);

namespace Innmind\LogReader\Reader;

use Innmind\LogReader\{
    Reader,
    Log\Stream
};
use Innmind\Filesystem\File;
use Innmind\Immutable\{
    StreamInterface,
    Str
};

/**
 * Use a generator that will parse the file only when you'll manipulate the
 * stream returned
 */
final class OnDemand implements Reader
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
        return new Stream(function(File $file) {
            $content = $file->content();
            $line = new Str('');

            while (!$content->end()) {
                $line = $line->append((string) $content->read(8192));
                $splits = $line->split("\n");

                if ($splits->size() > 2) {
                    $line = $splits->last();
                    $lines = $splits->dropEnd(1);

                    foreach ($lines as $line) {
                        yield ($this->parse)($line);
                    }
                }
            }
        }, $file);
    }
}
