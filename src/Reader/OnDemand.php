<?php
declare(strict_types = 1);

namespace Innmind\LogReader\Reader;

use Innmind\LogReader\{
    Reader,
    Log\Stream
};
use Innmind\Filesystem\File;
use Innmind\Immutable\StreamInterface;

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
            $content->rewind();

            while (!$content->end()) {
                $line = $content->readLine();

                if ($line->length() === 0) {
                    continue;
                }

                yield ($this->parse)($line);
            }
        }, $file);
    }
}
