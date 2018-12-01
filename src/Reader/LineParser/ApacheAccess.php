<?php
declare(strict_types = 1);

namespace Innmind\LogReader\Reader\LineParser;

use Innmind\LogReader\{
    Reader\LineParser,
    Log,
    Log\Attribute\Attribute,
};
use Innmind\TimeContinuum\{
    TimeContinuumInterface,
    FormatInterface,
};
use Innmind\Url\{
    Url,
    Authority\Host,
};
use Innmind\Http\{
    ProtocolVersion\ProtocolVersion,
    Message\Method\Method,
    Message\StatusCode\StatusCode,
};
use Innmind\Immutable\Str;

final class ApacheAccess implements LineParser
{
    private const FORMAT = '~^(?P<client>\S+) - (?P<user>\S+) \[(?P<time>\d{2}/[a-zA-Z]{3}/\d{4}:\d{2}:\d{2}:\d{2} [+\-]\d{4})] "(?P<method>[A-Z]{3,}) (?P<path>.+) HTTP/(?P<protocol>\d\.\d)" (?P<code>\d+) (?P<size>\d+)$~';

    private $clock;

    public function __construct(TimeContinuumInterface $clock)
    {
        $this->clock = $clock;
    }

    public function __invoke(Str $line): Log
    {
        $parts = $line->capture(self::FORMAT);
        $protocol = $parts->get('protocol')->split('.');

        return new Log(
            $this->clock->at(
                (string) $parts->get('time'),
                new class implements FormatInterface {
                    public function __toString(): string
                    {
                        return 'd/M/Y:H:i:s O';
                    }
                }
            ),
            $line,
            new Attribute('user', $parts->get('user')),
            new Attribute('client', new Host((string) $parts->get('client'))),
            new Attribute('method', new Method((string) $parts->get('method'))),
            new Attribute('path', Url::fromString((string) $parts->get('path'))),
            new Attribute('protocol', new ProtocolVersion(
                (int) (string) $protocol->first(),
                (int) (string) $protocol->last()
            )),
            new Attribute('code', new StatusCode(
                (int) (string) $parts->get('code')
            )),
            new Attribute('size', (int) (string) $parts->get('size'))
        );
    }
}
