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
use Innmind\Filesystem\{
    File,
    Stream\Stream
};
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
        $reader = new Synchronous(new Symfony(new Earth));
        $file = new File(
            'symfony.log',
            Stream::fromPath('fixtures/symfony.log')
        );

        $stream = $reader->parse($file);

        $this->assertInstanceOf(StaticStream::class, $stream);
        $this->assertSame(Log::class, (string) $stream->type());
        $this->assertCount(5000, $stream);
    }
}