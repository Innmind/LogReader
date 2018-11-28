<?php
declare(strict_types = 1);

namespace Tests\Innmind\LogReader\Reader;

use Innmind\LogReader\{
    Reader\Synchronous,
    Reader\LineParser\Symfony,
    Reader,
    Log
};
use Innmind\TimeContinuum\TimeContinuum\Earth;
use Innmind\Stream\Readable\Stream;
use Innmind\Immutable\Stream as StaticStream;
use PHPUnit\Framework\TestCase;

class SynchronousTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            Reader::class,
            new Synchronous(new Symfony(new Earth))
        );
    }

    public function testParse()
    {
        $read = new Synchronous(new Symfony(new Earth));
        $file = new Stream(fopen('fixtures/symfony.log', 'r'));

        $stream = $read($file);

        $this->assertInstanceOf(StaticStream::class, $stream);
        $this->assertSame(Log::class, (string) $stream->type());
        $this->assertCount(5000, $stream);
    }
}
