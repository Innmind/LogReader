<?php
declare(strict_types = 1);

namespace Innmind\LogReader;

use Innmind\LogReader\Log\Attribute;
use Innmind\TimeContinuum\PointInTimeInterface;
use Innmind\Immutable\{
    Str,
    MapInterface,
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
            throw new \TypeError(sprintf(
                'Argument 3 must be of type MapInterface<string, %s>',
                Attribute::class
            ));
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

    public function equals(self $log): bool
    {
        return $this->raw->equals($log->raw());
    }

    public function __toString(): string
    {
        return (string) $this->raw;
    }
}
