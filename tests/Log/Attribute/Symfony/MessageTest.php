<?php
declare(strict_types = 1);

namespace Tests\Innmind\LogReader\Log\Attribute\Symfony;

use Innmind\LogReader\Log\{
    Attribute\Symfony\Message,
    Attribute
};
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    public function testInterface()
    {
        $message = new Message('Fatal error');

        $this->assertInstanceOf(Attribute::class, $message);
        $this->assertSame('message', $message->key());
        $this->assertSame('Fatal error', $message->value());
    }

    /**
     * @expectedException Innmind\LogReader\Exception\LogicException
     */
    public function testThrowWhenEmptyMessage()
    {
        new Message('');
    }
}
