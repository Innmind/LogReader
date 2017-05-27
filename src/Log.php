<?php
declare(strict_types = 1);

namespace Innmind\LogReader;

use Innmind\LogReader\{
    Log\Attribute,
    Exception\InvalidAttributes
};
use Innmind\TimeContinuum\PointInTimeInterface;
use Innmind\Immutable\{
    Str,
    MapInterface
};

final class Log
{
    private $time;
    private $raw;
    private $attributes;

    public function __construct(
        PointInTimeInterface $time,
        Str $raw,
        MapInterface $attributes
    ) {
        if (
            (string) $attributes->keyType() !== 'string' ||
            (string) $attributes->valueType() !== Attribute::class
        ) {
            throw new InvalidAttributes;
        }

        $this->time = $time;
        $this->raw = $raw;
        $this->attributes = $attributes;
    }

    public function time(): PointInTimeInterface
    {
        return $this->time;
    }

    public function raw(): Str
    {
        return $this->raw;
    }

    /**
     * @return MapInterface<string, Attribute>
     */
    public function attributes(): MapInterface
    {
        return $this->attributes;
    }

    public function __toString(): string
    {
        return (string) $this->raw;
    }
}
