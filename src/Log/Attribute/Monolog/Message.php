<?php
declare(strict_types = 1);

namespace Innmind\LogReader\Log\Attribute\Monolog;

use Innmind\LogReader\Log\Attribute;
use Innmind\Immutable\{
    Maybe,
    Str,
};

/**
 * @psalm-immutable
 */
final class Message implements Attribute
{
    private function __construct(private string $value)
    {
    }

    /**
     * @psalm-pure
     *
     * @return Maybe<self>
     */
    public static function maybe(Str $value): Maybe
    {
        return Maybe::just($value)
            ->filter(static fn($value) => !$value->empty())
            ->map(static fn($value) => new self($value->toString()));
    }

    #[\Override]
    public function key(): string
    {
        return 'message';
    }

    #[\Override]
    public function value(): string
    {
        return $this->value;
    }
}
