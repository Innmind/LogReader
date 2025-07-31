<?php
declare(strict_types = 1);

namespace Innmind\LogReader\Log;

/**
 * @psalm-immutable
 */
interface Attribute
{
    #[\NoDiscard]
    public function key(): string;
    #[\NoDiscard]
    public function value(): mixed;
}
