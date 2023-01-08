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

    private function __construct(string $key, mixed $value)
    {
        if (Str::of($key)->empty()) {
            throw new EmptyAttributeKeyNotAllowed;
        }

        $this->key = $key;
        $this->value = $value;
    }

    /**
     * @psalm-pure
     *
     * @param literal-string $key
     *
     * @throws EmptyAttributeKeyNotAllowed
     */
    public static function of(string $key, mixed $value): self
    {
        return new self($key, $value);
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
