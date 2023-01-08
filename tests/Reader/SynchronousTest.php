<?php
declare(strict_types = 1);

namespace Tests\Innmind\LogReader\Reader;

use Innmind\LogReader\{
    Reader\Synchronous,
    Reader\LineParser\Monolog,
    Reader,
    Log,
};
use Innmind\TimeContinuum\Earth\Clock;
use Innmind\Stream\Readable\Stream;
use Innmind\Immutable\Sequence;
use PHPUnit\Framework\TestCase;

class SynchronousTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            Reader::class,
            new Synchronous(new Monolog(new Clock)),
        );
    }

    public function testParse()
    {
        $read = new Synchronous(new Monolog(new Clock));
        $file = new Stream(\fopen('fixtures/symfony.log', 'r'));

        $stream = $read($file);

        $this->assertInstanceOf(Sequence::class, $stream);
        $this->assertSame(Log::class, (string) $stream->type());
        $this->assertCount(5000, $stream);
    }
}
