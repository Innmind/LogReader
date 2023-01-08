<?php
declare(strict_types = 1);

namespace Innmind\LogReader;

use Innmind\Filesystem\File\Content;
use Innmind\Immutable\Sequence;

interface Reader
{
    /**
     * @return Sequence<Log>
     */
    public function __invoke(Content $file): Sequence;
}
