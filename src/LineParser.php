<?php
declare(strict_types = 1);

namespace Innmind\LogReader;

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
