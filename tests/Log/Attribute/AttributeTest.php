<?php
declare(strict_types = 1);

namespace Tests\Innmind\LogReader\Log\Attribute;

use Innmind\LogReader\Log\{
    Attribute\Attribute,
    Attribute as AttributeInterface
};
use PHPUnit\Framework\TestCase;

class AttributeTest extends TestCase
{
    public function testInterface()
    {
        $attribute = new Attribute('foo', 42);

        $this->assertInstanceOf(AttributeInterface::class, $attribute);
        $this->assertSame('foo', $attribute->key());
        $this->assertSame(42, $attribute->value());
    }

    /**
     * @expectedException Innmind\LogReader\Exception\EmptyAttributeKeyNotAllowed
     */
    public function testThrowWhenEmptyKey()
    {
        new Attribute('', 42);
    }
}
