<?php
declare(strict_types = 1);

namespace Innmind\LogReader\Log;

interface Attribute
{
    public function key(): string;
    public function value(): mixed;
}
