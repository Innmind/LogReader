<?php
declare(strict_types = 1);

namespace Tests\Innmind\LogReader\Reader;

use Innmind\LogReader\{
    Reader\OnDemand,
    Reader\LineParser\Symfony,
    Reader,
    Log,
    Log\Stream as OnDemandStream
};
use Innmind\TimeContinuum\TimeContinuum\Earth;
use Innmind\Filesystem\{
    File,
    Stream\Stream
};
use PHPUnit\Framework\TestCase;

class OnDemandTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            Reader::class,
            new OnDemand(new Symfony(new Earth))
        );
    }

    public function testParse()
    {
        $reader = new OnDemand(new Symfony(new Earth));
        $file = new File(
            'symfony.log',
            Stream::fromPath('fixtures/symfony.log')
        );

        $stream = $reader->parse($file);

        $this->assertInstanceOf(OnDemandStream::class, $stream);
        $this->assertSame(Log::class, (string) $stream->type());
        $this->assertCount(5000, $stream);
    }
}
