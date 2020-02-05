<?php
declare(strict_types = 1);

namespace Innmind\LogReader\Log\Attribute;

use Innmind\LogReader\{
    Log\Attribute as AttributeInterface,
    Exception\EmptyAttributeKeyNotAllowed,
};

final class Attribute implements AttributeInterface
{
    private string $key;
    private $value;

    public function __construct(string $key, $value)
    {
        if (empty($key)) {
            throw new EmptyAttributeKeyNotAllowed;
        }

        $this->key = $key;
        $this->value = $value;
    }

    public function key(): string
    {
        return $this->key;
    }

    /**
     * {@inheritdoc}
     */
    public function value()
    {
        return $this->value;
    }
}
