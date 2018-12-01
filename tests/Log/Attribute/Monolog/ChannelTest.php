<?php
declare(strict_types = 1);

namespace Tests\Innmind\LogReader\Log\Attribute\Monolog;

use Innmind\LogReader\Log\{
    Attribute\Monolog\Channel,
    Attribute
};
use PHPUnit\Framework\TestCase;

class ChannelTest extends TestCase
{
    public function testInterface()
    {
        $channel = new Channel('request');

        $this->assertInstanceOf(Attribute::class, $channel);
        $this->assertSame('channel', $channel->key());
        $this->assertSame('request', $channel->value());
    }

    /**
     * @expectedException Innmind\LogReader\Exception\DomainException
     */
    public function testThrowWhenEmptyChannel()
    {
        new Channel('');
    }
}
