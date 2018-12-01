<?php
declare(strict_types = 1);

namespace Tests\Innmind\LogReader;

use Innmind\LogReader\{
    Log,
    Log\Attribute,
};
use Innmind\TimeContinuum\PointInTimeInterface;
use Innmind\Immutable\{
    MapInterface,
    Str,
};
use PHPUnit\Framework\TestCase;

class LogTest extends TestCase
{
    public function testInterface()
    {
        $log = new Log(
            $time = $this->createMock(PointInTimeInterface::class),
            $raw = new Str('foo'),
            $attribute = new Attribute\Attribute('bar', 42)
        );

        $this->assertSame($time, $log->time());
        $this->assertSame($raw, $log->raw());
        $this->assertInstanceOf(MapInterface::class, $log->attributes());
        $this->assertSame('string', (string) $log->attributes()->keyType());
        $this->assertSame(Attribute::class, (string) $log->attributes()->valueType());
        $this->assertSame($attribute, $log->attributes()->get('bar'));
        $this->assertSame('foo', (string) $log);
    }

    public function testEquals()
    {
        $log = new Log(
            $this->createMock(PointInTimeInterface::class),
            new Str('foo')
        );
        $log2 = new Log(
            $this->createMock(PointInTimeInterface::class),
            new Str('bar')
        );

        $this->assertTrue($log->equals($log));
        $this->assertFalse($log->equals($log2));
    }
}
