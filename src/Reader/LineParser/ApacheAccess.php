<?php
declare(strict_types = 1);

namespace Innmind\LogReader\Reader\LineParser;

use Innmind\LogReader\{
    Reader\LineParser,
    Log,
    Log\Attribute
};
use Innmind\TimeContinuum\TimeContinuumInterface;
use Innmind\Url\{
    Url,
    Authority\Host
};
use Innmind\Http\{
    ProtocolVersion\ProtocolVersion,
    Message\Method\Method,
    Message\StatusCode\StatusCode
};
use Innmind\Immutable\{
    Str,
    Map
};

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
        $time = \DateTimeImmutable::createFromFormat(
            'd/M/Y:H:i:s O',
            (string) $parts->get('time')
        )->format(\DateTime::ATOM);

        return new Log(
            $this->clock->at($time),
            $line,
            (new Map('string', Attribute::class))
                ->put(
                    'user',
                    new Attribute\Attribute('user', $parts->get('user'))
                )
                ->put(
                    'client',
                    new Attribute\Attribute(
                        'client',
                        new Host((string) $parts->get('client'))
                    )
                )
                ->put(
                    'method',
                    new Attribute\Attribute(
                        'method',
                        new Method((string) $parts->get('method'))
                    )
                )
                ->put(
                    'path',
                    new Attribute\Attribute(
                        'path',
                        Url::fromString((string) $parts->get('path'))
                    )
                )
                ->put(
                    'protocol',
                    new Attribute\Attribute(
                        'protocol',
                        new ProtocolVersion(
                            (int) (string) $protocol->first(),
                            (int) (string) $protocol->last()
                        )
                    )
                )
                ->put(
                    'code',
                    new Attribute\Attribute(
                        'code',
                        new StatusCode((int) (string) $parts->get('code'))
                    )
                )
                ->put(
                    'size',
                    new Attribute\Attribute(
                        'size',
                        (int) (string) $parts->get('size')
                    )
                )
        );
    }
}
