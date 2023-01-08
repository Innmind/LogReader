<?php
declare(strict_types = 1);

namespace Tests\Innmind\LogReader\LineParser;

use Innmind\LogReader\{
    LineParser\Monolog,
    LineParser,
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
        $this->assertInstanceOf(LineParser::class, Monolog::of(new Clock));
    }

    /**
     * @dataProvider lines
     */
    public function testInvokation($line, $time, $channel, $level, $message, $context)
    {
        $parse = Monolog::of(new Clock(new UTC));

        $log = $parse(Str::of($line))->match(
            static fn($log) => $log,
            static fn() => null,
        );

        $this->assertInstanceOf(Log::class, $log);
        $this->assertSame($time, $log->time()->format(new ISO8601));
        $this->assertInstanceOf(
            Channel::class,
            $log
                ->attribute('channel')
                ->match(
                    static fn($attribute) => $attribute,
                    static fn() => null,
                ),
        );
        $this->assertSame(
            $channel,
            $log
                ->attribute('channel')
                ->match(
                    static fn($attribute) => $attribute->value(),
                    static fn() => null,
                ),
        );
        $this->assertInstanceOf(
            Level::class,
            $log
                ->attribute('level')
                ->match(
                    static fn($attribute) => $attribute,
                    static fn() => null,
                ),
        );
        $this->assertSame(
            $level,
            $log
                ->attribute('level')
                ->match(
                    static fn($attribute) => $attribute->value(),
                    static fn() => null,
                ),
        );
        $this->assertInstanceOf(
            Message::class,
            $log
                ->attribute('message')
                ->match(
                    static fn($attribute) => $attribute,
                    static fn() => null,
                ),
        );
        $this->assertSame(
            $message,
            $log
                ->attribute('message')
                ->match(
                    static fn($attribute) => $attribute->value(),
                    static fn() => null,
                ),
        );
        $this->assertSame(
            $context,
            $log
                ->attribute('context')
                ->match(
                    static fn($attribute) => $attribute->value(),
                    static fn() => null,
                ),
        );
        $this->assertSame(
            [],
            $log
                ->attribute('extra')
                ->match(
                    static fn($attribute) => $attribute->value(),
                    static fn() => null,
                ),
        );
    }

    public function testDoesntInjectContextAttributeWhenFailingToDecodeJsonString()
    {
        $parse = Monolog::of(new Clock(new UTC));

        $log = $parse(Str::of('[2017-02-08 07:01:04] php.INFO: User Deprecated: Not quoting the scalar "%innmind_neo4j.entity_factory.aggregate.class%" starting with the "%" indicator character is deprecated since Symfony 3.1 and will throw a ParseException in 4.0. {] []'))->match(
            static fn($log) => $log,
            static fn() => null,
        );

        $this->assertTrue(
            $log
                ->attribute('channel')
                ->match(
                    static fn() => true,
                    static fn() => false,
                ),
        );
        $this->assertTrue(
            $log
                ->attribute('level')
                ->match(
                    static fn() => true,
                    static fn() => false,
                ),
        );
        $this->assertTrue(
            $log
                ->attribute('message')
                ->match(
                    static fn() => true,
                    static fn() => false,
                ),
        );
        $this->assertTrue(
            $log
                ->attribute('extra')
                ->match(
                    static fn() => true,
                    static fn() => false,
                ),
        );
        $this->assertFalse(
            $log
                ->attribute('context')
                ->match(
                    static fn() => true,
                    static fn() => false,
                ),
        );
    }

    public function testDoesntInjectExtraAttributeWhenFailingToDecodeJsonString()
    {
        $parse = Monolog::of(new Clock(new UTC));

        $log = $parse(Str::of('[2017-02-08 07:01:04] php.INFO: User Deprecated: Not quoting the scalar "%innmind_neo4j.entity_factory.aggregate.class%" starting with the "%" indicator character is deprecated since Symfony 3.1 and will throw a ParseException in 4.0. [] {]'))->match(
            static fn($log) => $log,
            static fn() => null,
        );

        $this->assertTrue(
            $log
                ->attribute('channel')
                ->match(
                    static fn() => true,
                    static fn() => false,
                ),
        );
        $this->assertTrue(
            $log
                ->attribute('level')
                ->match(
                    static fn() => true,
                    static fn() => false,
                ),
        );
        $this->assertTrue(
            $log
                ->attribute('message')
                ->match(
                    static fn() => true,
                    static fn() => false,
                ),
        );
        $this->assertTrue(
            $log
                ->attribute('context')
                ->match(
                    static fn() => true,
                    static fn() => false,
                ),
        );
        $this->assertFalse(
            $log
                ->attribute('extra')
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
