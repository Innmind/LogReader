<?php
declare(strict_types = 1);

namespace Tests\Innmind\LogReader\Reader;

use Innmind\LogReader\{
    Reader\OnDemand,
    Reader\LineParser\Monolog,
    Reader,
    Log,
};
use Innmind\TimeContinuum\Earth\Clock;
use Innmind\Stream\Readable\Stream;
use Innmind\Immutable\Sequence;
use PHPUnit\Framework\TestCase;

class OnDemandTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(
            Reader::class,
            new OnDemand(new Monolog(new Clock))
        );
    }

    public function testParse()
    {
        $read = new OnDemand(new Monolog(new Clock));
        $file = new Stream(fopen('fixtures/symfony.log', 'r'));

        $stream = $read($file);

        $this->assertInstanceOf(Sequence::class, $stream);
        $this->assertSame(Log::class, (string) $stream->type());
        $this->assertCount(5000, $stream);
        $this->assertSame(
            'User Deprecated: Use Str class instead',
            $stream->last()->attributes()->get('message')->value()
        );
    }
}
