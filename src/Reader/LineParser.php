<?php
declare(strict_types = 1);

namespace Innmind\LogReader\Reader;

use Innmind\LogReader\Log;
use Innmind\Immutable\{
    Str,
    Maybe,
};

interface LineParser
{
    /**
     * @return Maybe<Log>
     */
    public function __invoke(Str $line): Maybe;
}
