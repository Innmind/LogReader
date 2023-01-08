<?php
declare(strict_types = 1);

namespace Innmind\LogReader\Log\Attribute\Monolog;

use Innmind\LogReader\{
    Log\Attribute,
    Exception\DomainException,
};
use Psr\Log\LogLevel;

/**
 * @psalm-immutable
 */
final class Level implements Attribute
{
    private string $value;

    public function __construct(string $value)
    {
        if (!\defined($level = LogLevel::class.'::'.$value)) {
            throw new DomainException;
        }

        /** @var string */
        $this->value = \constant($level);
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
