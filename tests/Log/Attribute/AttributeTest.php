<?php
declare(strict_types = 1);

namespace Tests\Innmind\LogReader\Log\Attribute;

use Innmind\LogReader\{
    Log\Attribute\Attribute,
    Log\Attribute as AttributeInterface,
    Exception\EmptyAttributeKeyNotAllowed
};
use PHPUnit\Framework\TestCase;

class AttributeTest extends TestCase
{
    public function testInterface()
    {
        $attribute = Attribute::of('foo', 42);

        $this->assertInstanceOf(AttributeInterface::class, $attribute);
        $this->assertSame('foo', $attribute->key());
        $this->assertSame(42, $attribute->value());
    }

    public function testThrowWhenEmptyKey()
    {
        $this->expectException(EmptyAttributeKeyNotAllowed::class);

        Attribute::of('', 42);
    }
}
