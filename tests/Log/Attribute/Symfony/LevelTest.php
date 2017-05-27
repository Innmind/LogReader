<?php
declare(strict_types = 1);

namespace Tests\Innmind\LogReader\Log\Attribute\Symfony;

use Innmind\LogReader\Log\{
    Attribute\Symfony\Level,
    Attribute
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
     * @expectedException Innmind\LogReader\Exception\LogicException
     */
    public function testThrowWhenUnknownLevel()
    {
        new Level('whatever');
    }
}
