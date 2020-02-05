<?php
declare(strict_types = 1);

namespace Innmind\LogReader;

use Innmind\Stream\Readable;
use Innmind\Immutable\Sequence;

interface Reader
{
    /**
     * @return Sequence<Log>
     */
    public function __invoke(Readable $file): Sequence;
}
