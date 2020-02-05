<?php
declare(strict_types = 1);

namespace Tests\Innmind\LogReader\Log\Attribute\Monolog;

use Innmind\LogReader\{
    Log\Attribute\Monolog\Message,
    Log\Attribute,
    Exception\DomainException
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

    public function testThrowWhenEmptyMessage()
    {
        $this->expectException(DomainException::class);

        new Message('');
    }
}
