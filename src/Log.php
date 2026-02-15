<?php
declare(strict_types = 1);

namespace Innmind\LogReader;

use Innmind\LogReader\Log\Attribute;
use Innmind\Time\Point;
use Innmind\Immutable\{
    Str,
    Set,
    Maybe,
};

/**
 * @psalm-immutable
 */
final class Log
{
    /**
     * @param Set<Attribute> $attributes
     */
    private function __construct(
        private Point $time,
        private Str $raw,
        private Set $attributes,
    ) {
    }

    /**
     * @psalm-pure
     *
     * @param Set<Attribute> $attributes
     */
    public static function of(Point $time, Str $raw, Set $attributes): self
    {
        return new self($time, $raw, $attributes);
    }

    #[\NoDiscard]
    public function time(): Point
    {
        return $this->time;
    }

    #[\NoDiscard]
    public function raw(): Str
    {
        return $this->raw;
    }

    /**
     * @return Set<Attribute>
     */
    #[\NoDiscard]
    public function attributes(): Set
    {
        return $this->attributes;
    }

    /**
     * @return Maybe<Attribute>
     */
    #[\NoDiscard]
    public function attribute(string $key): Maybe
    {
        return $this->attributes->find(static fn($attribute) => $attribute->key() === $key);
    }

    #[\NoDiscard]
    public function equals(self $log): bool
    {
        return $this->raw->equals($log->raw());
    }

    #[\NoDiscard]
    public function toString(): string
    {
        return $this->raw->toString();
    }
}
