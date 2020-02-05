<?php
declare(strict_types = 1);

namespace Innmind\LogReader\Log\Attribute\Monolog;

use Innmind\LogReader\{
    Log\Attribute,
    Exception\DomainException,
};
use Innmind\Immutable\Str;

final class Message implements Attribute
{
    private string $value;

    public function __construct(string $value)
    {
        if (Str::of($value)->empty()) {
            throw new DomainException;
        }

        $this->value = $value;
    }

    public function key(): string
    {
        return 'message';
    }

    public function value(): string
    {
        return $this->value;
    }
}
