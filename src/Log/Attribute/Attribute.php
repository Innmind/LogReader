<?php
declare(strict_types = 1);

namespace Innmind\LogReader\Log\Attribute;

use Innmind\LogReader\{
    Log\Attribute as AttributeInterface,
    Exception\EmptyAttributeKeyNotAllowed,
};
use Innmind\Immutable\Str;

/**
 * @psalm-immutable
 */
final class Attribute implements AttributeInterface
{
    private string $key;
    /** @var mixed */
    private $value;

    /**
     * @param mixed $value
     */
    public function __construct(string $key, $value)
    {
        if (Str::of($key)->empty()) {
            throw new EmptyAttributeKeyNotAllowed;
        }

        $this->key = $key;
        $this->value = $value;
    }

    public function key(): string
    {
        return $this->key;
    }

    public function value(): mixed
    {
        return $this->value;
    }
}
