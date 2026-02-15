<?php
declare(strict_types = 1);

namespace Tests\Innmind\LogReader\LineParser;

use Innmind\LogReader\{
    LineParser\ApacheAccess,
    LineParser,
    Log,
};
use Innmind\Time\{
    Clock,
    Format,
};
use Innmind\Http\{
    Method,
    Response\StatusCode,
    ProtocolVersion,
};
use Innmind\Url\{
    Url,
    Authority\Host,
};
use Innmind\Immutable\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Innmind\BlackBox\PHPUnit\Framework\TestCase;
use Composer\InstalledVersions;

class ApacheAccessTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(LineParser::class, ApacheAccess::of(Clock::live()));
    }

    #[DataProvider('lines')]
    public function testInvokation($line, $client, $user, $time, $method, $path, $protocol, $code, $size)
    {
        if (\is_array($path)) {
            [$v84, $v85] = $path;

            if (\str_starts_with(InstalledVersions::getVersion('innmind/url'), '5.0')) {
                $path = $v84;
            } else {
                $path = $v85;
            }
        }

        $parse = ApacheAccess::of(
            Clock::live()->switch(static fn($timezones) => $timezones->utc()),
        );

        $log = $parse(Str::of($line))->match(
            static fn($log) => $log,
            static fn() => null,
        );

        $this->assertInstanceOf(Log::class, $log);
        $this->assertSame($time, $log->time()->format(Format::iso8601()));
        $this->assertInstanceOf(
            Host::class,
            $log
                ->attribute('client')
                ->match(
                    static fn($attribute) => $attribute->value(),
                    static fn() => null,
                ),
        );
        $this->assertSame(
            $client,
            $log
                ->attribute('client')
                ->match(
                    static fn($attribute) => $attribute->value()->toString(),
                    static fn() => null,
                ),
        );
        $this->assertSame(
            $user,
            $log
                ->attribute('user')
                ->match(
                    static fn($attribute) => $attribute->value()->toString(),
                    static fn() => null,
                ),
        );
        $this->assertInstanceOf(
            Url::class,
            $log
                ->attribute('path')
                ->match(
                    static fn($attribute) => $attribute->value(),
                    static fn() => null,
                ),
        );
        $this->assertSame(
            $path,
            $log
                ->attribute('path')
                ->match(
                    static fn($attribute) => $attribute->value()->toString(),
                    static fn() => null,
                ),
        );
        $this->assertInstanceOf(
            Method::class,
            $log
                ->attribute('method')
                ->match(
                    static fn($attribute) => $attribute->value(),
                    static fn() => null,
                ),
        );
        $this->assertSame(
            $method,
            $log
                ->attribute('method')
                ->match(
                    static fn($attribute) => $attribute->value()->toString(),
                    static fn() => null,
                ),
        );
        $this->assertInstanceOf(
            ProtocolVersion::class,
            $log
                ->attribute('protocol')
                ->match(
                    static fn($attribute) => $attribute->value(),
                    static fn() => null,
                ),
        );
        $this->assertSame(
            $protocol,
            $log
                ->attribute('protocol')
                ->match(
                    static fn($attribute) => $attribute->value()->toString(),
                    static fn() => null,
                ),
        );
        $this->assertInstanceOf(
            StatusCode::class,
            $log
                ->attribute('code')
                ->match(
                    static fn($attribute) => $attribute->value(),
                    static fn() => null,
                ),
        );
        $this->assertSame(
            $code,
            $log
                ->attribute('code')
                ->match(
                    static fn($attribute) => $attribute->value()->toString(),
                    static fn() => null,
                ),
        );
        $this->assertSame(
            $size,
            $log
                ->attribute('size')
                ->match(
                    static fn($attribute) => $attribute->value(),
                    static fn() => null,
                ),
        );
    }

    public static function lines(): array
    {
        return [
            [
                '64.242.88.10 - - [07/Mar/2004:16:05:49 -0800] "GET /twiki/bin/edit/Main/Double_bounce_sender?topicparent=Main.ConfigurationVariables HTTP/1.1" 401 12846',
                '64.242.88.10',
                '-',
                '2004-03-08T00:05:49+00:00',
                'GET',
                '/twiki/bin/edit/Main/Double_bounce_sender?topicparent=Main.ConfigurationVariables',
                '1.1',
                '401',
                12846,
            ],
            [
                '64.242.88.10 - - [07/Mar/2004:16:06:51 -0800] "GET /twiki/bin/rdiff/TWiki/NewUserTemplate?rev1=1.3&rev2=1.2 HTTP/1.1" 200 4523',
                '64.242.88.10',
                '-',
                '2004-03-08T00:06:51+00:00',
                'GET',
                '/twiki/bin/rdiff/TWiki/NewUserTemplate?rev1=1.3&rev2=1.2',
                '1.1',
                '200',
                4523,
            ],
            [
                '64.242.88.10 - - [07/Mar/2004:16:10:02 -0800] "GET /mailman/listinfo/hsdivision HTTP/1.1" 200 6291',
                '64.242.88.10',
                '-',
                '2004-03-08T00:10:02+00:00',
                'GET',
                '/mailman/listinfo/hsdivision',
                '1.1',
                '200',
                6291,
            ],
            [
                '64.242.88.10 - - [07/Mar/2004:16:11:58 -0800] "GET /twiki/bin/view/TWiki/WikiSyntax HTTP/1.1" 200 7352',
                '64.242.88.10',
                '-',
                '2004-03-08T00:11:58+00:00',
                'GET',
                '/twiki/bin/view/TWiki/WikiSyntax',
                '1.1',
                '200',
                7352,
            ],
            [
                '64.242.88.10 - - [07/Mar/2004:16:20:55 -0800] "GET /twiki/bin/view/Main/DCCAndPostFix HTTP/1.1" 200 5253',
                '64.242.88.10',
                '-',
                '2004-03-08T00:20:55+00:00',
                'GET',
                '/twiki/bin/view/Main/DCCAndPostFix',
                '1.1',
                '200',
                5253,
            ],
            [
                '64.242.88.10 - - [07/Mar/2004:16:23:12 -0800] "GET /twiki/bin/oops/TWiki/AppendixFileSystem?template=oopsmore¶m1=1.12¶m2=1.12 HTTP/1.1" 200 11382',
                '64.242.88.10',
                '-',
                '2004-03-08T00:23:12+00:00',
                'GET',
                [
                    '/twiki/bin/oops/TWiki/AppendixFileSystem?template=oopsmore¶m1=1.12¶m2=1.12',
                    '/twiki/bin/oops/TWiki/AppendixFileSystem?template=oopsmore%C2%B6m1=1.12%C2%B6m2=1.12',
                ],
                '1.1',
                '200',
                11382,
            ],
            [
                '64.242.88.10 - - [07/Mar/2004:16:24:16 -0800] "GET /twiki/bin/view/Main/PeterThoeny HTTP/1.1" 200 4924',
                '64.242.88.10',
                '-',
                '2004-03-08T00:24:16+00:00',
                'GET',
                '/twiki/bin/view/Main/PeterThoeny',
                '1.1',
                '200',
                4924,
            ],
            [
                '64.242.88.10 - - [07/Mar/2004:16:29:16 -0800] "GET /twiki/bin/edit/Main/Header_checks?topicparent=Main.ConfigurationVariables HTTP/1.1" 401 12851',
                '64.242.88.10',
                '-',
                '2004-03-08T00:29:16+00:00',
                'GET',
                '/twiki/bin/edit/Main/Header_checks?topicparent=Main.ConfigurationVariables',
                '1.1',
                '401',
                12851,
            ],
            [
                '64.242.88.10 - - [07/Mar/2004:16:30:29 -0800] "GET /twiki/bin/attach/Main/OfficeLocations HTTP/1.1" 401 12851',
                '64.242.88.10',
                '-',
                '2004-03-08T00:30:29+00:00',
                'GET',
                '/twiki/bin/attach/Main/OfficeLocations',
                '1.1',
                '401',
                12851,
            ],
            [
                '64.242.88.10 - - [07/Mar/2004:16:31:48 -0800] "GET /twiki/bin/view/TWiki/WebTopicEditTemplate HTTP/1.1" 200 3732',
                '64.242.88.10',
                '-',
                '2004-03-08T00:31:48+00:00',
                'GET',
                '/twiki/bin/view/TWiki/WebTopicEditTemplate',
                '1.1',
                '200',
                3732,
            ],
            [
                '64.242.88.10 - - [07/Mar/2004:16:32:50 -0800] "GET /twiki/bin/view/Main/WebChanges HTTP/1.1" 200 40520',
                '64.242.88.10',
                '-',
                '2004-03-08T00:32:50+00:00',
                'GET',
                '/twiki/bin/view/Main/WebChanges',
                '1.1',
                '200',
                40520,
            ],
            [
                '64.242.88.10 - - [07/Mar/2004:16:33:53 -0800] "GET /twiki/bin/edit/Main/Smtpd_etrn_restrictions?topicparent=Main.ConfigurationVariables HTTP/1.1" 401 12851',
                '64.242.88.10',
                '-',
                '2004-03-08T00:33:53+00:00',
                'GET',
                '/twiki/bin/edit/Main/Smtpd_etrn_restrictions?topicparent=Main.ConfigurationVariables',
                '1.1',
                '401',
                12851,
            ],
            [
                '64.242.88.10 - - [07/Mar/2004:16:35:19 -0800] "GET /mailman/listinfo/business HTTP/1.1" 200 6379',
                '64.242.88.10',
                '-',
                '2004-03-08T00:35:19+00:00',
                'GET',
                '/mailman/listinfo/business',
                '1.1',
                '200',
                6379,
            ],
            [
                '64.242.88.10 - - [07/Mar/2004:16:36:22 -0800] "GET /twiki/bin/rdiff/Main/WebIndex?rev1=1.2&rev2=1.1 HTTP/1.1" 200 46373',
                '64.242.88.10',
                '-',
                '2004-03-08T00:36:22+00:00',
                'GET',
                '/twiki/bin/rdiff/Main/WebIndex?rev1=1.2&rev2=1.1',
                '1.1',
                '200',
                46373,
            ],
            [
                '64.242.88.10 - - [07/Mar/2004:16:37:27 -0800] "GET /twiki/bin/view/TWiki/DontNotify HTTP/1.1" 200 4140',
                '64.242.88.10',
                '-',
                '2004-03-08T00:37:27+00:00',
                'GET',
                '/twiki/bin/view/TWiki/DontNotify',
                '1.1',
                '200',
                4140,
            ],
            [
                '64.242.88.10 - - [07/Mar/2004:16:39:24 -0800] "GET /twiki/bin/view/Main/TokyoOffice HTTP/1.1" 200 3853',
                '64.242.88.10',
                '-',
                '2004-03-08T00:39:24+00:00',
                'GET',
                '/twiki/bin/view/Main/TokyoOffice',
                '1.1',
                '200',
                3853,
            ],
            [
                '64.242.88.10 - - [07/Mar/2004:16:43:54 -0800] "GET /twiki/bin/view/Main/MikeMannix HTTP/1.1" 200 3686',
                '64.242.88.10',
                '-',
                '2004-03-08T00:43:54+00:00',
                'GET',
                '/twiki/bin/view/Main/MikeMannix',
                '1.1',
                '200',
                3686,
            ],
            [
                '64.242.88.10 - - [07/Mar/2004:16:45:56 -0800] "GET /twiki/bin/attach/Main/PostfixCommands HTTP/1.1" 401 12846',
                '64.242.88.10',
                '-',
                '2004-03-08T00:45:56+00:00',
                'GET',
                '/twiki/bin/attach/Main/PostfixCommands',
                '1.1',
                '401',
                12846,
            ],
            [
                '64.242.88.10 - - [07/Mar/2004:16:47:12 -0800] "GET /robots.txt HTTP/1.1" 200 68',
                '64.242.88.10',
                '-',
                '2004-03-08T00:47:12+00:00',
                'GET',
                '/robots.txt',
                '1.1',
                '200',
                68,
            ],
            [
                '64.242.88.10 - - [07/Mar/2004:16:47:46 -0800] "GET /twiki/bin/rdiff/Know/ReadmeFirst?rev1=1.5&rev2=1.4 HTTP/1.1" 200 5724',
                '64.242.88.10',
                '-',
                '2004-03-08T00:47:46+00:00',
                'GET',
                '/twiki/bin/rdiff/Know/ReadmeFirst?rev1=1.5&rev2=1.4',
                '1.1',
                '200',
                5724,
            ],
            [
                '64.242.88.10 - - [07/Mar/2004:16:49:04 -0800] "GET /twiki/bin/view/Main/TWikiGroups?rev=1.2 HTTP/1.1" 200 5162',
                '64.242.88.10',
                '-',
                '2004-03-08T00:49:04+00:00',
                'GET',
                '/twiki/bin/view/Main/TWikiGroups?rev=1.2',
                '1.1',
                '200',
                5162,
            ],
            [
                '64.242.88.10 - - [07/Mar/2004:16:50:54 -0800] "GET /twiki/bin/rdiff/Main/ConfigurationVariables HTTP/1.1" 200 59679',
                '64.242.88.10',
                '-',
                '2004-03-08T00:50:54+00:00',
                'GET',
                '/twiki/bin/rdiff/Main/ConfigurationVariables',
                '1.1',
                '200',
                59679,
            ],
            [
                '64.242.88.10 - - [07/Mar/2004:16:52:35 -0800] "GET /twiki/bin/edit/Main/Flush_service_name?topicparent=Main.ConfigurationVariables HTTP/1.1" 401 12851',
                '64.242.88.10',
                '-',
                '2004-03-08T00:52:35+00:00',
                'GET',
                '/twiki/bin/edit/Main/Flush_service_name?topicparent=Main.ConfigurationVariables',
                '1.1',
                '401',
                12851,
            ],
            [
                '64.242.88.10 - - [07/Mar/2004:16:53:46 -0800] "GET /twiki/bin/rdiff/TWiki/TWikiRegistration HTTP/1.1" 200 34395',
                '64.242.88.10',
                '-',
                '2004-03-08T00:53:46+00:00',
                'GET',
                '/twiki/bin/rdiff/TWiki/TWikiRegistration',
                '1.1',
                '200',
                34395,
            ],
            [
                'lj1036.inktomisearch.com - - [07/Mar/2004:17:18:36 -0800] "GET /robots.txt HTTP/1.0" 200 68',
                'lj1036.inktomisearch.com',
                '-',
                '2004-03-08T01:18:36+00:00',
                'GET',
                '/robots.txt',
                '1.0',
                '200',
                68,
            ],
            [
                'lj1090.inktomisearch.com - - [07/Mar/2004:17:18:41 -0800] "GET /twiki/bin/view/Main/LondonOffice HTTP/1.0" 200 3860',
                'lj1090.inktomisearch.com',
                '-',
                '2004-03-08T01:18:41+00:00',
                'GET',
                '/twiki/bin/view/Main/LondonOffice',
                '1.0',
                '200',
                3860,
            ],
        ];
    }
}
