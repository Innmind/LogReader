<?php
declare(strict_types = 1);

namespace Innmind\LogReader;

use Innmind\Filesystem\FileInterface;
use Innmind\Immutable\StreamInterface;

interface Reader
{
    /**
     * @return StreamInterface<Log>
     */
    public function parse(FileInterface $file): StreamInterface;
}
