<?php
declare(strict_types = 1);

namespace Innmind\LogReader\Reader;

use Innmind\LogReader\Log;
use Innmind\Immutable\Str;

interface LineParser
{
    public function __invoke(Str $line): Log;
}
