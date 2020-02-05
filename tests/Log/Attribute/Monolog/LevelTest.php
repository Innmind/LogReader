<?php
declare(strict_types = 1);

namespace Tests\Innmind\LogReader\Log\Attribute\Monolog;

use Innmind\LogReader\{
    Log\Attribute\Monolog\Level,
    Log\Attribute,
    Exception\DomainException
};
use PHPUnit\Framework\TestCase;

class LevelTest extends TestCase
{
    public function testInterface()
    {
        $level = new Level('CRITICAL');

        $this->assertInstanceOf(Attribute::class, $level);
        $this->assertSame('level', $level->key());
        $this->assertSame('critical', $level->value());
    }

    public function testThrowWhenUnknownLevel()
    {
        $this->expectException(DomainException::class);

        new Level('whatever');
    }
}
