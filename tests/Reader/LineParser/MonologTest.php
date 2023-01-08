<?php
declare(strict_types = 1);

namespace Tests\Innmind\LogReader\Reader\LineParser;

use Innmind\LogReader\{
    Reader\LineParser\Monolog,
    Reader\LineParser,
    Log,
    Log\Attribute\Monolog\Channel,
    Log\Attribute\Monolog\Level,
    Log\Attribute\Monolog\Message,
};
use Innmind\TimeContinuum\Earth\{
    Clock,
    Format\ISO8601,
    Timezone\UTC,
};
use Innmind\Immutable\Str;
use PHPUnit\Framework\TestCase;

class MonologTest extends TestCase
{
    public function testInterface()
    {
        $this->assertInstanceOf(LineParser::class, new Monolog(new Clock));
    }

    /**
     * @dataProvider lines
     */
    public function testInvokation($line, $time, $channel, $level, $message, $context)
    {
        $parse = new Monolog(new Clock(new UTC));

        $log = $parse(Str::of($line))->match(
            static fn($log) => $log,
            static fn() => null,
        );

        $this->assertInstanceOf(Log::class, $log);
        $this->assertSame($time, $log->time()->format(new ISO8601));
        $this->assertInstanceOf(
            Channel::class,
            $log
                ->attributes()
                ->find(static fn($attribute) => $attribute->key() === 'channel')
                ->match(
                    static fn($attribute) => $attribute,
                    static fn() => null,
                ),
        );
        $this->assertSame(
            $channel,
            $log
                ->attributes()
                ->find(static fn($attribute) => $attribute->key() === 'channel')
                ->match(
                    static fn($attribute) => $attribute->value(),
                    static fn() => null,
                ),
        );
        $this->assertInstanceOf(
            Level::class,
            $log
                ->attributes()
                ->find(static fn($attribute) => $attribute->key() === 'level')
                ->match(
                    static fn($attribute) => $attribute,
                    static fn() => null,
                ),
        );
        $this->assertSame(
            $level,
            $log
                ->attributes()
                ->find(static fn($attribute) => $attribute->key() === 'level')
                ->match(
                    static fn($attribute) => $attribute->value(),
                    static fn() => null,
                ),
        );
        $this->assertInstanceOf(
            Message::class,
            $log
                ->attributes()
                ->find(static fn($attribute) => $attribute->key() === 'message')
                ->match(
                    static fn($attribute) => $attribute,
                    static fn() => null,
                ),
        );
        $this->assertSame(
            $message,
            $log
                ->attributes()
                ->find(static fn($attribute) => $attribute->key() === 'message')
                ->match(
                    static fn($attribute) => $attribute->value(),
                    static fn() => null,
                ),
        );
        $this->assertSame(
            $context,
            $log
                ->attributes()
                ->find(static fn($attribute) => $attribute->key() === 'context')
                ->match(
                    static fn($attribute) => $attribute->value(),
                    static fn() => null,
                ),
        );
        $this->assertSame(
            [],
            $log
                ->attributes()
                ->find(static fn($attribute) => $attribute->key() === 'extra')
                ->match(
                    static fn($attribute) => $attribute->value(),
                    static fn() => null,
                ),
        );
    }

    public function testParseWithCustomRegexp()
    {
        $parse = new Monolog(
            new Clock(new UTC),
            '~^\[(?P<time>\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}).000000\] (?P<channel>[a-zA-Z-_]+)\.(?P<level>EMERGENCY|ALERT|CRITICAL|ERROR|WARNING|NOTICE|INFO|DEBUG): (?P<message>.+) (?P<context>[\{\[].*[\]\}]) (?P<extra>[\{\[].*[\]\}])$~',
        );

        $log = $parse(Str::of('[2017-02-08 07:01:04.000000] php.INFO: User Deprecated: Not quoting the scalar "%innmind_neo4j.entity_factory.aggregate.class%" starting with the "%" indicator character is deprecated since Symfony 3.1 and will throw a ParseException in 4.0. {"exception":"[object] (ErrorException(code: 0): User Deprecated: Not quoting the scalar \"%innmind_neo4j.entity_factory.aggregate.class%\" starting with the \"%\" indicator character is deprecated since Symfony 3.1 and will throw a ParseException in 4.0. at /Users/baptouuuu/Sites/Innmind/API/vendor/symfony/symfony/src/Symfony/Component/Yaml/Inline.php:325)"} []'))->match(
            static fn($log) => $log,
            static fn() => null,
        );

        $this->assertInstanceOf(Log::class, $log);
        $this->assertSame('2017-02-08T07:01:04+00:00', $log->time()->format(new ISO8601));
    }

    public function testDoesntInjectContextAttributeWhenFailingToDecodeJsonString()
    {
        $parse = new Monolog(new Clock(new UTC));

        $log = $parse(Str::of('[2017-02-08 07:01:04] php.INFO: User Deprecated: Not quoting the scalar "%innmind_neo4j.entity_factory.aggregate.class%" starting with the "%" indicator character is deprecated since Symfony 3.1 and will throw a ParseException in 4.0. {] []'))->match(
            static fn($log) => $log,
            static fn() => null,
        );

        $this->assertTrue(
            $log
                ->attributes()
                ->find(static fn($attribute) => $attribute->key() === 'channel')
                ->match(
                    static fn() => true,
                    static fn() => false,
                ),
        );
        $this->assertTrue(
            $log
                ->attributes()
                ->find(static fn($attribute) => $attribute->key() === 'level')
                ->match(
                    static fn() => true,
                    static fn() => false,
                ),
        );
        $this->assertTrue(
            $log
                ->attributes()
                ->find(static fn($attribute) => $attribute->key() === 'message')
                ->match(
                    static fn() => true,
                    static fn() => false,
                ),
        );
        $this->assertTrue(
            $log
                ->attributes()
                ->find(static fn($attribute) => $attribute->key() === 'extra')
                ->match(
                    static fn() => true,
                    static fn() => false,
                ),
        );
        $this->assertFalse(
            $log
                ->attributes()
                ->find(static fn($attribute) => $attribute->key() === 'context')
                ->match(
                    static fn() => true,
                    static fn() => false,
                ),
        );
    }

    public function testDoesntInjectExtraAttributeWhenFailingToDecodeJsonString()
    {
        $parse = new Monolog(new Clock(new UTC));

        $log = $parse(Str::of('[2017-02-08 07:01:04] php.INFO: User Deprecated: Not quoting the scalar "%innmind_neo4j.entity_factory.aggregate.class%" starting with the "%" indicator character is deprecated since Symfony 3.1 and will throw a ParseException in 4.0. [] {]'))->match(
            static fn($log) => $log,
            static fn() => null,
        );

        $this->assertTrue(
            $log
                ->attributes()
                ->find(static fn($attribute) => $attribute->key() === 'channel')
                ->match(
                    static fn() => true,
                    static fn() => false,
                ),
        );
        $this->assertTrue(
            $log
                ->attributes()
                ->find(static fn($attribute) => $attribute->key() === 'level')
                ->match(
                    static fn() => true,
                    static fn() => false,
                ),
        );
        $this->assertTrue(
            $log
                ->attributes()
                ->find(static fn($attribute) => $attribute->key() === 'message')
                ->match(
                    static fn() => true,
                    static fn() => false,
                ),
        );
        $this->assertTrue(
            $log
                ->attributes()
                ->find(static fn($attribute) => $attribute->key() === 'context')
                ->match(
                    static fn() => true,
                    static fn() => false,
                ),
        );
        $this->assertFalse(
            $log
                ->attributes()
                ->find(static fn($attribute) => $attribute->key() === 'extra')
                ->match(
                    static fn() => true,
                    static fn() => false,
                ),
        );
    }

    public function lines(): array
    {
        return [
            [
                '[2017-02-08 07:01:04] php.INFO: User Deprecated: Not quoting the scalar "%innmind_neo4j.entity_factory.aggregate.class%" starting with the "%" indicator character is deprecated since Symfony 3.1 and will throw a ParseException in 4.0. {"exception":"[object] (ErrorException(code: 0): User Deprecated: Not quoting the scalar \"%innmind_neo4j.entity_factory.aggregate.class%\" starting with the \"%\" indicator character is deprecated since Symfony 3.1 and will throw a ParseException in 4.0. at /Users/baptouuuu/Sites/Innmind/API/vendor/symfony/symfony/src/Symfony/Component/Yaml/Inline.php:325)"} []',
                '2017-02-08T07:01:04+00:00',
                'php',
                'info',
                'User Deprecated: Not quoting the scalar "%innmind_neo4j.entity_factory.aggregate.class%" starting with the "%" indicator character is deprecated since Symfony 3.1 and will throw a ParseException in 4.0.',
                [
                    'exception' => '[object] (ErrorException(code: 0): User Deprecated: Not quoting the scalar "%innmind_neo4j.entity_factory.aggregate.class%" starting with the "%" indicator character is deprecated since Symfony 3.1 and will throw a ParseException in 4.0. at /Users/baptouuuu/Sites/Innmind/API/vendor/symfony/symfony/src/Symfony/Component/Yaml/Inline.php:325)',
                ],
            ],
            [
                '[2017-02-08 07:01:04] php.INFO: User Deprecated: Use Str class instead {"exception":"[object] (ErrorException(code: 0): User Deprecated: Use Str class instead at /Users/baptouuuu/Sites/Innmind/API/vendor/innmind/immutable/src/StringPrimitive.php:31)"} []',
                '2017-02-08T07:01:04+00:00',
                'php',
                'info',
                'User Deprecated: Use Str class instead',
                [
                    'exception' => '[object] (ErrorException(code: 0): User Deprecated: Use Str class instead at /Users/baptouuuu/Sites/Innmind/API/vendor/innmind/immutable/src/StringPrimitive.php:31)',
                ],
            ],
            [
                '[2017-02-02 07:30:45] php.CRITICAL: Fatal Error: Uncaught Symfony\Component\Debug\Exception\FatalThrowableError: Type error: Too few arguments to function AppBundle\EventListener\ExceptionListener::__construct(), 0 passed in /var/www/var/cache/prod/appProdProjectContainer.php on line 3025 and exactly 1 expected in /var/www/src/AppBundle/EventListener/ExceptionListener.php:21 Stack trace: #0 /var/www/var/cache/prod/appProdProjectContainer.php(3025): AppBundle\EventListener\ExceptionListener->__construct() #1 /var/www/var/cache/prod/classes.php(3270): appProdProjectContainer->getListener_ExceptionService() #2 /var/www/var/cache/prod/classes.php(3561): Symfony\Component\DependencyInjection\Container->get(\'listener.except...\') #3 /var/www/var/cache/prod/classes.php(3530): Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher->lazyLoad(\'kernel.exceptio...\') #4 /var/www/var/cache/prod/classes.php(3378): Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher->getListeners(\'kernel.exceptio...\') #5 /var/www/var/cache/prod/classes.php(4438): Symfon {"exception":"[object] (Symfony\\\\Component\\\\Debug\\\\Exception\\\\FatalErrorException(code: 0): Error: Uncaught Symfony\\\\Component\\\\Debug\\\\Exception\\\\FatalThrowableError: Type error: Too few arguments to function AppBundle\\\\EventListener\\\\ExceptionListener::__construct(), 0 passed in /var/www/var/cache/prod/appProdProjectContainer.php on line 3025 and exactly 1 expected in /var/www/src/AppBundle/EventListener/ExceptionListener.php:21\\\nStack trace:\\\n#0 /var/www/var/cache/prod/appProdProjectContainer.php(3025): AppBundle\\\\EventListener\\\\ExceptionListener->__construct()\\\n#1 /var/www/var/cache/prod/classes.php(3270): appProdProjectContainer->getListener_ExceptionService()\\\n#2 /var/www/var/cache/prod/classes.php(3561): Symfony\\\\Component\\\\DependencyInjection\\\\Container->get(\'listener.except...\')\\\n#3 /var/www/var/cache/prod/classes.php(3530): Symfony\\\\Component\\\\EventDispatcher\\\\ContainerAwareEventDispatcher->lazyLoad(\'kernel.exceptio...\')\\\n#4 /var/www/var/cache/prod/classes.php(3378): Symfony\\\\Component\\\\EventDispatcher\\\\ContainerAwareEventDispatcher->getListeners(\'kernel.exceptio...\')\\\n#5 /var/www/var/cache/prod/classes.php(4438): Symfon at /var/www/src/AppBundle/EventListener/ExceptionListener.php:21)"} []',
                '2017-02-02T07:30:45+00:00',
                'php',
                'critical',
                'Fatal Error: Uncaught Symfony\Component\Debug\Exception\FatalThrowableError: Type error: Too few arguments to function AppBundle\EventListener\ExceptionListener::__construct(), 0 passed in /var/www/var/cache/prod/appProdProjectContainer.php on line 3025 and exactly 1 expected in /var/www/src/AppBundle/EventListener/ExceptionListener.php:21 Stack trace: #0 /var/www/var/cache/prod/appProdProjectContainer.php(3025): AppBundle\EventListener\ExceptionListener->__construct() #1 /var/www/var/cache/prod/classes.php(3270): appProdProjectContainer->getListener_ExceptionService() #2 /var/www/var/cache/prod/classes.php(3561): Symfony\Component\DependencyInjection\Container->get(\'listener.except...\') #3 /var/www/var/cache/prod/classes.php(3530): Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher->lazyLoad(\'kernel.exceptio...\') #4 /var/www/var/cache/prod/classes.php(3378): Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher->getListeners(\'kernel.exceptio...\') #5 /var/www/var/cache/prod/classes.php(4438): Symfon',
                [
                    'exception' => '[object] (Symfony\Component\Debug\Exception\FatalErrorException(code: 0): Error: Uncaught Symfony\Component\Debug\Exception\FatalThrowableError: Type error: Too few arguments to function AppBundle\EventListener\ExceptionListener::__construct(), 0 passed in /var/www/var/cache/prod/appProdProjectContainer.php on line 3025 and exactly 1 expected in /var/www/src/AppBundle/EventListener/ExceptionListener.php:21\nStack trace:\n#0 /var/www/var/cache/prod/appProdProjectContainer.php(3025): AppBundle\EventListener\ExceptionListener->__construct()\n#1 /var/www/var/cache/prod/classes.php(3270): appProdProjectContainer->getListener_ExceptionService()\n#2 /var/www/var/cache/prod/classes.php(3561): Symfony\Component\DependencyInjection\Container->get(\'listener.except...\')\n#3 /var/www/var/cache/prod/classes.php(3530): Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher->lazyLoad(\'kernel.exceptio...\')\n#4 /var/www/var/cache/prod/classes.php(3378): Symfony\Component\EventDispatcher\ContainerAwareEventDispatcher->getListeners(\'kernel.exceptio...\')\n#5 /var/www/var/cache/prod/classes.php(4438): Symfon at /var/www/src/AppBundle/EventListener/ExceptionListener.php:21)',
                ],
            ],
        ];
    }
}
