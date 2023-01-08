<?php
declare(strict_types = 1);

namespace Innmind\LogReader\Reader\LineParser;

use Innmind\LogReader\{
    Reader\LineParser,
    Log,
    Log\Attribute\Attribute,
};
use Innmind\TimeContinuum\{
    Clock,
    PointInTime,
};
use Innmind\Url\{
    Url,
    Authority\Host,
};
use Innmind\Http\{
    ProtocolVersion,
    Message\Method,
    Message\StatusCode,
};
use Innmind\Immutable\{
    Str,
    Maybe,
    Set,
};

final class ApacheAccess implements LineParser
{
    private const FORMAT = '~^(?P<client>\S+) - (?P<user>\S+) \[(?P<time>\d{2}/[a-zA-Z]{3}/\d{4}:\d{2}:\d{2}:\d{2} [+\-]\d{4})] "(?P<method>[A-Z]{3,}) (?P<path>.+) HTTP/(?P<protocol>\d\.\d)" (?P<code>\d+) (?P<size>\d+)$~';

    private Clock $clock;

    public function __construct(Clock $clock)
    {
        $this->clock = $clock;
    }

    public function __invoke(Str $line): Maybe
    {
        $parts = $line->capture(self::FORMAT);
        $protocol = $parts
            ->get('protocol')
            ->map(static fn($protocol) => $protocol->split('.'))
            ->flatMap(
                static fn($parts) => Maybe::all(
                    $parts
                        ->first()
                        ->map(static fn($major) => (int) $major->toString()),
                    $parts
                        ->last()
                        ->map(static fn($minor) => (int) $minor->toString()),
                )
                    ->flatMap(ProtocolVersion::maybe(...)),
            )
            ->map(static fn($protocol) => new Attribute('protocol', $protocol));
        $time = $parts
            ->get('time')
            ->map(static fn($time) => $time->toString())
            ->flatMap(fn($time) => $this->clock->at($time, new Apache\TimeFormat));
        $user = $parts
            ->get('user')
            ->map(static fn($user) => new Attribute('user', $user));
        $client = $parts
            ->get('client')
            ->map(static fn($client) => $client->toString())
            ->map(Host::of(...))
            ->map(static fn($client) => new Attribute('client', $client));
        $method = $parts
            ->get('method')
            ->map(static fn($method) => $method->toString())
            ->flatMap(Method::maybe(...))
            ->map(static fn($method) => new Attribute('method', $method));
        $path = $parts
            ->get('path')
            ->map(static fn($path) => $path->toString())
            ->map(Url::of(...))
            ->map(static fn($path) => new Attribute('path', $path));
        $code = $parts
            ->get('code')
            ->map(static fn($code) => (int) $code->toString())
            ->flatMap(StatusCode::maybe(...))
            ->map(static fn($code) => new Attribute('code', $code));
        $size = $parts
            ->get('size')
            ->map(static fn($size) => (int) $size->toString())
            ->map(static fn($size) => new Attribute('size', $size));

        /**
         * @psalm-suppress NamedArgumentNotAllowed
         * @psalm-suppress InvalidArgument
         */
        return Maybe::all(
            $time,
            $user,
            $client,
            $method,
            $path,
            $protocol,
            $code,
            $size,
        )
            ->map(static fn(PointInTime $time, Attribute ...$attributes) => new Log(
                $time,
                $line,
                Set::of(...$attributes),
            ));
    }
}
