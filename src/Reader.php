<?php
declare(strict_types = 1);

namespace Innmind\LogReader;

use Innmind\Filesystem\File;
use Innmind\Immutable\StreamInterface;

interface Reader
{
    /**
     * @return StreamInterface<Log>
     */
    public function __invoke(File $file): StreamInterface;
}
