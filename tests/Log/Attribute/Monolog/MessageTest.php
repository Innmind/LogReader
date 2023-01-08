<?php
declare(strict_types = 1);

namespace Tests\Innmind\LogReader\Log\Attribute\Monolog;

use Innmind\LogReader\{
    Log\Attribute\Monolog\Message,
    Log\Attribute,
};
use Innmind\Immutable\Str;
use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    public function testInterface()
    {
        $message = Message::maybe(Str::of('Fatal error'))->match(
            static fn($message) => $message,
            static fn() => null,
        );

        $this->assertInstanceOf(Attribute::class, $message);
        $this->assertSame('message', $message->key());
        $this->assertSame('Fatal error', $message->value());
    }

    public function testReturnNothingWhenEmptyMessage()
    {
        $this->assertNull(Message::maybe(Str::of(''))->match(
            static fn($message) => $message,
            static fn() => null,
        ));
    }
}
