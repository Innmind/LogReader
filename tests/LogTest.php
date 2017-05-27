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
     * @expectedException Innmind\LogReader\Exception\InvalidAttributes
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
     * @expectedException Innmind\LogReader\Exception\InvalidAttributes
     */
    public function testThrowWhenInvalidAttributesValues()
    {
        new Log(
            $this->createMock(PointInTimeInterface::class),
            new Str(''),
            new Map('string', 'string')
        );
    }
}
