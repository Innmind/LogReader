<?php
declare(strict_types = 1);

namespace Tests\Innmind\LogReader;

use Innmind\LogReader\{
    Reader,
    LineParser\Monolog,
    Log,
};
use Innmind\TimeContinuum\Earth\Clock;
use Innmind\Filesystem\File\Content;
use Innmind\IO\IO;
use Innmind\Stream\Readable\Stream;
use Innmind\Immutable\Sequence;
use PHPUnit\Framework\TestCase;

class ReaderTest extends TestCase
{
    public function testParse()
    {
        $read = Reader::of(Monolog::of(new Clock));
        $file = Content::oneShot(
            IO::of(static fn() => null)->readable()->wrap(
                Stream::of(\fopen('fixtures/symfony.log', 'r')),
            ),
        );

        $stream = $read($file);

        $this->assertInstanceOf(Sequence::class, $stream);
        $this->assertCount(5000, $stream);
    }
}
