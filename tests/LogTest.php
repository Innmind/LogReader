<?php
declare(strict_types = 1);

namespace Tests\Innmind\LogReader;

use Innmind\LogReader\{
    Log,
    Log\Attribute
};
use Innmind\TimeContinuum\PointInTimeInterface;
use Innmind\Immutable\{
    Map,
    Str
};
use PHPUnit\Framework\TestCase;

class LogTest extends TestCase
{
    public function testInterface()
    {
        $log = new Log(
            $time = $this->createMock(PointInTimeInterface::class),
            $raw = new Str('foo'),
            $attributes = new Map('string', Attribute::class)
        );

        $this->assertSame($time, $log->time());
        $this->assertSame($raw, $log->raw());
        $this->assertSame($attributes, $log->attributes());
        $this->assertSame('foo', (string) $log);
    }

    /**
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 3 must be of type MapInterface<string, Innmind\LogReader\Log\Attribute>
     */
    public function testThrowWhenInvalidAttributesKeys()
    {
        new Log(
            $this->createMock(PointInTimeInterface::class),
            new Str(''),
            new Map('int', Attribute::class)
        );
    }

    /**
     * @expectedException TypeError
     * @expectedExceptionMessage Argument 3 must be of type MapInterface<string, Innmind\LogReader\Log\Attribute>
     */
    public function testThrowWhenInvalidAttributesValues()
    {
        new Log(
            $this->createMock(PointInTimeInterface::class),
            new Str(''),
            new Map('string', 'string')
        );
    }

    public function testEquals()
    {
        $log = new Log(
            $this->createMock(PointInTimeInterface::class),
            new Str('foo'),
            new Map('string', Attribute::class)
        );
        $log2 = new Log(
            $this->createMock(PointInTimeInterface::class),
            new Str('bar'),
            new Map('string', Attribute::class)
        );

        $this->assertTrue($log->equals($log));
        $this->assertFalse($log->equals($log2));
    }
}
