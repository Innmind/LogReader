<?php
declare(strict_types = 1);

namespace Innmind\LogReader\Reader\LineParser;

use Innmind\LogReader\{
    Reader\LineParser,
    Log,
    Log\Attribute\Attribute,
};
use Innmind\TimeContinuum\Clock;
use Innmind\Url\{
    Url,
    Authority\Host,
};
use Innmind\Http\{
    ProtocolVersion,
    Message\Method,
    Message\StatusCode,
};
use Innmind\Immutable\Str;

final class ApacheAccess implements LineParser
{
    private const FORMAT = '~^(?P<client>\S+) - (?P<user>\S+) \[(?P<time>\d{2}/[a-zA-Z]{3}/\d{4}:\d{2}:\d{2}:\d{2} [+\-]\d{4})] "(?P<method>[A-Z]{3,}) (?P<path>.+) HTTP/(?P<protocol>\d\.\d)" (?P<code>\d+) (?P<size>\d+)$~';

    private Clock $clock;

    public function __construct(Clock $clock)
    {
        $this->clock = $clock;
    }

    public function __invoke(Str $line): Log
    {
        $parts = $line->capture(self::FORMAT);
        $protocol = $parts->get('protocol')->split('.');

        return new Log(
            $this->clock->at(
                $parts->get('time')->toString(),
                new Apache\TimeFormat,
            ),
            $line,
            new Attribute('user', $parts->get('user')),
            new Attribute('client', Host::of($parts->get('client')->toString())),
            new Attribute('method', new Method($parts->get('method')->toString())),
            new Attribute('path', Url::of($parts->get('path')->toString())),
            new Attribute('protocol', new ProtocolVersion(
                (int) $protocol->first()->toString(),
                (int) $protocol->last()->toString(),
            )),
            new Attribute('code', new StatusCode(
                (int) $parts->get('code')->toString(),
            )),
            new Attribute('size', (int) $parts->get('size')->toString()),
        );
    }
}
