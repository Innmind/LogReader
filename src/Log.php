<?php
declare(strict_types = 1);

namespace Innmind\LogReader;

use Innmind\LogReader\Log\Attribute;
use Innmind\TimeContinuum\PointInTimeInterface;
use Innmind\Immutable\{
    Str,
    MapInterface,
    Map,
    Sequence,
};

final class Log
{
    private PointInTimeInterface $time;
    private Str $raw;
    private Map $attributes;

    public function __construct(
        PointInTimeInterface $time,
        Str $raw,
        Attribute ...$attributes
    ) {
        $this->time = $time;
        $this->raw = $raw;
        $this->attributes = Sequence::of(...$attributes)->reduce(
            Map::of('string', Attribute::class),
            static function(MapInterface $attributes, Attribute $attribute): MapInterface {
                return $attributes->put($attribute->key(), $attribute);
            }
        );
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
