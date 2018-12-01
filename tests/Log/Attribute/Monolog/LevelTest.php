<?php
declare(strict_types = 1);

namespace Tests\Innmind\LogReader\Log\Attribute\Monolog;

use Innmind\LogReader\Log\{
    Attribute\Monolog\Level,
    Attribute,
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

    /**
     * @expectedException Innmind\LogReader\Exception\DomainException
     */
    public function testThrowWhenUnknownLevel()
    {
        new Level('whatever');
    }
}
