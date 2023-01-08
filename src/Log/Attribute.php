<?php
declare(strict_types = 1);

namespace Innmind\LogReader\Log;

/**
 * @psalm-immutable
 */
interface Attribute
{
    public function key(): string;
    public function value(): mixed;
}
