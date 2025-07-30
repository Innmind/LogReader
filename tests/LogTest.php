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
use Innmind\BlackBox\PHPUnit\Framework\TestCase;

class LogTest extends TestCase
{
    public function testInterface()
    {
        $log = Log::of(
            $time = PointInTime::now(),
            $raw = Str::of('foo'),
            $attributes = Set::of(Attribute\Attribute::of('bar', 42)),
        );

        $this->assertSame($time, $log->time());
        $this->assertSame($raw, $log->raw());
        $this->assertInstanceOf(Set::class, $log->attributes());
        $this->assertSame($attributes, $log->attributes());
        $this->assertSame('foo', $log->toString());
    }

    public function testEquals()
    {
        $log = Log::of(
            PointInTime::now(),
            Str::of('foo'),
            Set::of(),
        );
        $log2 = Log::of(
            PointInTime::now(),
            Str::of('bar'),
            Set::of(),
        );

        $this->assertTrue($log->equals($log));
        $this->assertFalse($log->equals($log2));
    }
}
