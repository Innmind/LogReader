<?php
declare(strict_types = 1);

namespace Innmind\LogReader\Log\Attribute\Symfony;

use Innmind\LogReader\{
    Log\Attribute,
    Exception\LogicException
};
use Psr\Log\LogLevel;

final class Level implements Attribute
{
    private $value;

    public function __construct(string $value)
    {
        if (!defined($level = LogLevel::class.'::'.$value)) {
            throw new LogicException;
        }

        $this->value = constant($level);
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
