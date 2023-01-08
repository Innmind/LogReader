<?php
declare(strict_types = 1);

namespace Tests\Innmind\LogReader\LineParser;

use Innmind\LogReader\{
    LineParser\ApacheAccess,
    LineParser,
    Log,
};
use Innmind\TimeContinuum\Earth\{
    Clock,
    Timezone\UTC,
    Format\ISO8601,
};
use Innmind\Http\{
    Message\Method,
    Message\StatusCode,
    ProtocolVersion,
};
use Innmind\Url\{
    Url,
    Authority\Host,
};
use Innmind\Immutable\Str;
use PHPUnit\Framework\TestCase;

class ApacheAccessTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(LineParser::class, ApacheAccess::of(new Clock));
    }

    /**
     * @dataProvider lines
     */
    public function testInvokation($line, $client, $user, $time, $method, $path, $protocol, $code, $size)
    {
        $parse = ApacheAccess::of(new Clock(new UTC(-8)));

        $log = $parse(Str::of($line))->match(
            static fn($log) => $log,
            static fn() => null,
        );

        $this->assertInstanceOf(Log::class, $log);
        $this->assertSame($time, $log->time()->format(new ISO8601));
        $this->assertInstanceOf(
            Host::class,
            $log
                ->attributes()
                ->find(static fn($attribute) => $attribute->key() === 'client')
                ->match(
                    static fn($attribute) => $attribute->value(),
                    static fn() => null,
                ),
        );
        $this->assertSame(
            $client,
            $log
                ->attributes()
                ->find(static fn($attribute) => $attribute->key() === 'client')
                ->match(
                    static fn($attribute) => $attribute->value()->toString(),
                    static fn() => null,
                ),
        );
        $this->assertSame(
            $user,
            $log
                ->attributes()
                ->find(static fn($attribute) => $attribute->key() === 'user')
                ->match(
                    static fn($attribute) => $attribute->value()->toString(),
                    static fn() => null,
                ),
        );
        $this->assertInstanceOf(
            Url::class,
            $log
                ->attributes()
                ->find(static fn($attribute) => $attribute->key() === 'path')
                ->match(
                    static fn($attribute) => $attribute->value(),
                    static fn() => null,
                ),
        );
        $this->assertSame(
            $path,
            $log
                ->attributes()
                ->find(static fn($attribute) => $attribute->key() === 'path')
                ->match(
                    static fn($attribute) => $attribute->value()->toString(),
                    static fn() => null,
                ),
        );
        $this->assertInstanceOf(
            Method::class,
            $log
                ->attributes()
                ->find(static fn($attribute) => $attribute->key() === 'method')
                ->match(
                    static fn($attribute) => $attribute->value(),
                    static fn() => null,
                ),
        );
        $this->assertSame(
            $method,
            $log
                ->attributes()
                ->find(static fn($attribute) => $attribute->key() === 'method')
                ->match(
                    static fn($attribute) => $attribute->value()->toString(),
                    static fn() => null,
                ),
        );
        $this->assertInstanceOf(
            ProtocolVersion::class,
            $log
                ->attributes()
                ->find(static fn($attribute) => $attribute->key() === 'protocol')
                ->match(
                    static fn($attribute) => $attribute->value(),
                    static fn() => null,
                ),
        );
        $this->assertSame(
            $protocol,
            $log
                ->attributes()
                ->find(static fn($attribute) => $attribute->key() === 'protocol')
                ->match(
                    static fn($attribute) => $attribute->value()->toString(),
                    static fn() => null,
                ),
        );
        $this->assertInstanceOf(
            StatusCode::class,
            $log
                ->attributes()
                ->find(static fn($attribute) => $attribute->key() === 'code')
                ->match(
                    static fn($attribute) => $attribute->value(),
                    static fn() => null,
                ),
        );
        $this->assertSame(
            $code,
            $log
                ->attributes()
                ->find(static fn($attribute) => $attribute->key() === 'code')
                ->match(
                    static fn($attribute) => $attribute->value()->toString(),
                    static fn() => null,
                ),
        );
        $this->assertSame(
            $size,
            $log
                ->attributes()
                ->find(static fn($attribute) => $attribute->key() === 'size')
                ->match(
                    static fn($attribute) => $attribute->value(),
                    static fn() => null,
                ),
        );
    }

    public function lines(): array
    {
        return [
            [
                '64.242.88.10 - - [07/Mar/2004:16:05:49 -0800] "GET /twiki/bin/edit/Main/Double_bounce_sender?topicparent=Main.ConfigurationVariables HTTP/1.1" 401 12846',
                '64.242.88.10',
                '-',
                '2004-03-07T16:05:49-08:00',
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
                '2004-03-07T16:06:51-08:00',
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
                '2004-03-07T16:10:02-08:00',
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
                '2004-03-07T16:11:58-08:00',
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
                '2004-03-07T16:20:55-08:00',
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
                '2004-03-07T16:23:12-08:00',
                'GET',
                '/twiki/bin/oops/TWiki/AppendixFileSystem?template=oopsmore¶m1=1.12¶m2=1.12',
                '1.1',
                '200',
                11382,
            ],
            [
                '64.242.88.10 - - [07/Mar/2004:16:24:16 -0800] "GET /twiki/bin/view/Main/PeterThoeny HTTP/1.1" 200 4924',
                '64.242.88.10',
                '-',
                '2004-03-07T16:24:16-08:00',
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
                '2004-03-07T16:29:16-08:00',
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
                '2004-03-07T16:30:29-08:00',
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
                '2004-03-07T16:31:48-08:00',
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
                '2004-03-07T16:32:50-08:00',
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
                '2004-03-07T16:33:53-08:00',
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
                '2004-03-07T16:35:19-08:00',
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
                '2004-03-07T16:36:22-08:00',
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
                '2004-03-07T16:37:27-08:00',
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
                '2004-03-07T16:39:24-08:00',
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
                '2004-03-07T16:43:54-08:00',
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
                '2004-03-07T16:45:56-08:00',
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
                '2004-03-07T16:47:12-08:00',
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
                '2004-03-07T16:47:46-08:00',
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
                '2004-03-07T16:49:04-08:00',
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
                '2004-03-07T16:50:54-08:00',
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
                '2004-03-07T16:52:35-08:00',
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
                '2004-03-07T16:53:46-08:00',
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
                '2004-03-07T17:18:36-08:00',
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
                '2004-03-07T17:18:41-08:00',
                'GET',
                '/twiki/bin/view/Main/LondonOffice',
                '1.0',
                '200',
                3860,
            ],
        ];
    }
}
