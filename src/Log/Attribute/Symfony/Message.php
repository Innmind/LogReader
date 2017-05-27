<?php
declare(strict_types = 1);

namespace Innmind\LogReader\Log\Attribute\Symfony;

use Innmind\LogReader\{
    Log\Attribute,
    Exception\LogicException
};

final class Message implements Attribute
{
    private $value;

    public function __construct(string $value)
    {
        if (empty($value)) {
            throw new LogicException;
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
