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

final class Log
{
    private PointInTime $time;
    private Str $raw;
    /** @var Set<Attribute> */
    private Set $attributes;

    /**
     * @no-named-arguments
     */
    public function __construct(
        PointInTime $time,
        Str $raw,
        Attribute ...$attributes,
    ) {
        $this->time = $time;
        $this->raw = $raw;
        $this->attributes = Set::of(...$attributes);
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
