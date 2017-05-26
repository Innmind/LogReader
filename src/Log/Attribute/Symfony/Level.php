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
        if (!defined(LogLevel::class.'::'.$value)) {
            throw new LogicException;
        }

        $this->value = $value;
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
