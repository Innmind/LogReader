<?php
declare(strict_types = 1);

namespace Innmind\LogReader\Reader\LineParser\Apache;

use Innmind\TimeContinuum\Format;

/**
 * @psalm-immutable
 */
final class TimeFormat implements Format
{
    public function toString(): string
    {
        return 'd/M/Y:H:i:s O';
    }
}
