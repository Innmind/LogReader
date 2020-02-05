<?php
declare(strict_types = 1);

namespace Innmind\LogReader\Log\Attribute\Monolog;

use Innmind\LogReader\{
    Log\Attribute,
    Exception\DomainException,
};

final class Channel implements Attribute
{
    private string $value;

    public function __construct(string $value)
    {
        if (empty($value)) {
            throw new DomainException;
        }

        $this->value = $value;
    }

    public function key(): string
    {
        return 'channel';
    }

    public function value(): string
    {
        return $this->value;
    }
}
