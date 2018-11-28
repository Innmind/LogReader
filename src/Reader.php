<?php
declare(strict_types = 1);

namespace Innmind\LogReader;

use Innmind\Stream\Readable;
use Innmind\Immutable\StreamInterface;

interface Reader
{
    /**
     * @return StreamInterface<Log>
     */
    public function __invoke(Readable $file): StreamInterface;
}
