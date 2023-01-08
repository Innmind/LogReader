<?php
declare(strict_types = 1);

namespace Tests\Innmind\LogReader\Log\Attribute\Monolog;

use Innmind\LogReader\{
    Log\Attribute\Monolog\Level,
    Log\Attribute,
};
use PHPUnit\Framework\TestCase;

class LevelTest extends TestCase
{
    public function testInterface()
    {
        $level = Level::maybe('CRITICAL')->match(
            static fn($level) => $level,
            static fn() => null,
        );

        $this->assertInstanceOf(Attribute::class, $level);
        $this->assertSame('level', $level->key());
        $this->assertSame('critical', $level->value());
    }

    public function testReturnNothingWhenUnknownLevel()
    {
        $this->assertNull(Level::maybe('whatever')->match(
            static fn($level) => $level,
            static fn() => null,
        ));
    }
}
