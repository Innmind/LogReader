<?php
declare(strict_types = 1);

namespace Tests\Innmind\LogReader;

use Innmind\LogReader\{
    Reader,
    LineParser\Monolog,
};
use Innmind\Time\Clock;
use Innmind\Filesystem\{
    Adapter,
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
        $file = Adapter::mount(Path::of('fixtures/'))
            ->unwrap()
            ->get(Name::of('symfony.log'))
            ->match(
                static fn($file) => $file->content(),
                static fn() => null,
            );

        $stream = $read($file);

        $this->assertInstanceOf(Sequence::class, $stream);
        $this->assertSame(5000, $stream->size());
    }
}
