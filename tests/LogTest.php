<?php
declare(strict_types = 1);

namespace Tests\Innmind\LogReader;

use Innmind\LogReader\{
    Log,
    Log\Attribute,
};
use Innmind\TimeContinuum\PointInTime;
use Innmind\Immutable\{
    Set,
    Str,
};
use PHPUnit\Framework\TestCase;

class LogTest extends TestCase
{
    public function testInterface()
    {
        $log = new Log(
            $time = $this->createMock(PointInTime::class),
            $raw = Str::of('foo'),
            $attribute = new Attribute\Attribute('bar', 42),
        );

        $this->assertSame($time, $log->time());
        $this->assertSame($raw, $log->raw());
        $this->assertInstanceOf(Set::class, $log->attributes());
        $this->assertSame([$attribute], $log->attributes()->toList());
        $this->assertSame('foo', $log->toString());
    }

    public function testEquals()
    {
        $log = new Log(
            $this->createMock(PointInTime::class),
            Str::of('foo'),
        );
        $log2 = new Log(
            $this->createMock(PointInTime::class),
            Str::of('bar'),
        );

        $this->assertTrue($log->equals($log));
        $this->assertFalse($log->equals($log2));
    }
}
