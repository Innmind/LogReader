<?php
declare(strict_types = 1);

namespace Tests\Innmind\LogReader;

use Innmind\LogReader\{
    Reader,
    LineParser\Monolog,
};
use Innmind\TimeContinuum\Clock;
use Innmind\Filesystem\{
    Adapter\Filesystem,
    Name,
};
use Innmind\Url\Path;
use Innmind\Immutable\Sequence;
use Innmind\BlackBox\PHPUnit\Framework\TestCase;

class ReaderTest extends TestCase
{
    public function testParse()
    {
        $read = Reader::of(Monolog::of(Clock::live()));
        $file = Filesystem::mount(Path::of('fixtures/'))
            ->get(Name::of('symfony.log'))
            ->match(
                static fn($file) => $file->content(),
                static fn() => null,
            );

        $stream = $read($file);

        $this->assertInstanceOf(Sequence::class, $stream);
        $this->assertCount(5000, $stream);
    }
}
