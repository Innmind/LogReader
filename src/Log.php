<?php
declare(strict_types = 1);

namespace Innmind\LogReader;

use Innmind\LogReader\Log\Attribute;
use Innmind\TimeContinuum\PointInTime;
use Innmind\Immutable\{
    Str,
    Set,
    Sequence,
};

/**
 * @psalm-immutable
 */
final class Log
{
    private PointInTime $time;
    private Str $raw;
    /** @var Set<Attribute> */
    private Set $attributes;

    /**
     * @param Set<Attribute> $attributes
     */
    private function __construct(PointInTime $time, Str $raw, Set $attributes)
    {
        $this->time = $time;
        $this->raw = $raw;
        $this->attributes = $attributes;
    }

    /**
     * @psalm-pure
     *
     * @param Set<Attribute> $attributes
     */
    public static function of(PointInTime $time, Str $raw, Set $attributes): self
    {
        return new self($time, $raw, $attributes);
    }

    public function time(): PointInTime
    {
        return $this->time;
    }

    public function raw(): Str
    {
        return $this->raw;
    }

    /**
     * @return Set<Attribute>
     */
    public function attributes(): Set
    {
        return $this->attributes;
    }

    public function equals(self $log): bool
    {
        return $this->raw->equals($log->raw());
    }

    public function toString(): string
    {
        return $this->raw->toString();
    }
}
