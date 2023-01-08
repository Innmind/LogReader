<?php
declare(strict_types = 1);

namespace Tests\Innmind\LogReader\Log\Attribute\Monolog;

use Innmind\LogReader\{
    Log\Attribute\Monolog\Channel,
    Log\Attribute,
};
use Innmind\Immutable\Str;
use PHPUnit\Framework\TestCase;

class ChannelTest extends TestCase
{
    public function testInterface()
    {
        $channel = Channel::maybe(Str::of('request'))->match(
            static fn($channel) => $channel,
            static fn() => null,
        );

        $this->assertInstanceOf(Attribute::class, $channel);
        $this->assertSame('channel', $channel->key());
        $this->assertSame('request', $channel->value());
    }

    public function testReturnNothingWhenEmptyChannel()
    {
        $this->assertNull(Channel::maybe(Str::of(''))->match(
            static fn($channel) => $channel,
            static fn() => null,
        ));
    }
}
