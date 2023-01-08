<?php
declare(strict_types = 1);

namespace Innmind\LogReader;

use Innmind\LogReader\Log\Attribute;
use Innmind\TimeContinuum\PointInTime;
use Innmind\Immutable\{
    Str,
    Map,
    Sequence,
};

final class Log
{
    private PointInTime $time;
    private Str $raw;
    /** @var Map<string, Attribute> */
    private Map $attributes;

    public function __construct(
        PointInTime $time,
        Str $raw,
        Attribute ...$attributes,
    ) {
        $this->time = $time;
        $this->raw = $raw;
        $this->attributes = Sequence::of(Attribute::class, ...$attributes)->toMapOf(
            'string',
            Attribute::class,
            static function(Attribute $attribute): \Generator {
                yield $attribute->key() => $attribute;
            },
        );
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
     * @return Map<string, Attribute>
     */
    public function attributes(): Map
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
