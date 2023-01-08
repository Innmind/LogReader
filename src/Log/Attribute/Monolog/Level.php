<?php
declare(strict_types = 1);

namespace Innmind\LogReader\Log\Attribute\Monolog;

use Innmind\LogReader\Log\Attribute;
use Innmind\Immutable\{
    Maybe,
    Str,
};
use Psr\Log\LogLevel;

/**
 * @psalm-immutable
 */
final class Level implements Attribute
{
    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * @psalm-pure
     *
     * @return Maybe<self>
     */
    public static function maybe(string $value): Maybe
    {
        return Maybe::just($value)
            ->map(static fn($value) => LogLevel::class.'::'.$value)
            ->filter(\defined(...))
            ->map(\constant(...))
            ->map(static fn($value) => new self((string) $value));
    }

    public function key(): string
    {
        return 'level';
    }

    public function value(): string
    {
        return $this->value;
    }
}
