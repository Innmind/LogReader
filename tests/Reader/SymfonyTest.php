<?php
declare(strict_types = 1);

namespace Tests\Innmind\LogReader\Reader;

use Innmind\LogReader\{
    Reader\Symfony,
    Reader,
    Log
};
use Innmind\TimeContinuum\TimeContinuum\Earth;
use Innmind\Filesystem\{
    File,
    Stream\Stream
};
use Innmind\Immutable\StreamInterface;
use PHPUnit\Framework\TestCase;

class SymfonyTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(Reader::class, new Symfony(new Earth));
    }

    public function testParse()
    {
        $reader = new Symfony(new Earth);
        $file = new File(
            'symfony.log',
            Stream::fromPath('fixtures/symfony.log')
        );

        $stream = $reader->parse($file);

        $this->assertInstanceOf(StreamInterface::class, $stream);
        $this->assertSame(Log::class, (string) $stream->type());
        $this->assertCount(5000, $stream);
    }
}
