<?php
declare(strict_types = 1);

namespace Tests\Innmind\LogReader\Log;

use Innmind\LogReader\{
    Log\Stream,
    Log,
    Log\Attribute
};
use Innmind\TimeContinuum\PointInTime\Earth\Now;
use Innmind\Filesystem\File;
use Innmind\Immutable\{
    StreamInterface,
    Str,
    Map,
    Stream as GenericStream,
    MapInterface
};
use PHPUnit\Framework\TestCase;

class StreamTest extends TestCase
{
    private $stream;
    private $generator;

    public function setUp()
    {
        $this->stream = new Stream($this->generator = function() {
            $i = 0;
            while ($i < 10) {
                yield new Log(
                    new Now,
                    new Str((string) $i),
                    new Map('string', Attribute::class)
                );
                ++$i;
            }
        }, $this->createMock(File::class));
    }

    public function testInterface()
    {
        $this->assertInstanceOf(
            StreamInterface::class,
            $this->stream
        );
    }

    public function testType()
    {
        $this->assertInstanceOf(Str::class, $this->stream->type());
        $this->assertSame(Log::class, (string) $this->stream->type());
    }

    public function testIterator()
    {
        $this->stream->rewind();
        $count = 0;

        foreach ($this->stream as $key => $log) {
            $this->assertSame((string) $key, (string) $log);
            ++$count;
        }

        $this->assertSame(10, $count);
    }

    public function testGet()
    {
        $this->assertInstanceOf(Log::class, $this->stream->get(0));
        $this->assertInstanceOf(Log::class, $this->stream->get(1));
        $this->assertInstanceOf(Log::class, $this->stream->get(9));
    }

    /**
     * @expectedException Innmind\Immutable\Exception\OutOfBoundException
     */
    public function testThrowWhenGettingUnknownIndex()
    {
        $this->stream->get(10);
    }

    public function testDiff()
    {
        $diff = $this->stream->diff(new GenericStream(Log::class));

        $this->assertInstanceOf(StreamInterface::class, $diff);
        $this->assertSame(Log::class, (string) $diff->type());
        $this->assertCount(10, $diff);
    }

    public function testDistinct()
    {
        $this->assertSame($this->stream, $this->stream->distinct());
    }

    public function testDrop()
    {
        $stream = $this->stream->drop(2);

        $this->assertInstanceOf(StreamInterface::class, $stream);
        $this->assertSame(Log::class, (string) $stream->type());
        $this->assertSame('0123456789', (string) $this->stream->join(''));
        $this->assertSame('23456789', (string) $stream->join(''));
    }

    public function testDropEnd()
    {
        $stream = $this->stream->dropEnd(2);

        $this->assertInstanceOf(StreamInterface::class, $stream);
        $this->assertSame(Log::class, (string) $stream->type());
        $this->assertSame('0123456789', (string) $this->stream->join(''));
        $this->assertSame('01234567', (string) $stream->join(''));
    }

    public function testEquals()
    {
        //always false as we rewalk the full file each time
        $this->assertFalse($this->stream->equals($this->stream));
        $this->assertFalse($this->stream->equals($this->stream->clear()));
    }

    public function testFilter()
    {
        $stream = $this->stream->filter(function(Log $log): bool {
            return (int) (string) $log % 2 === 0;
        });

        $this->assertInstanceOf(StreamInterface::class, $stream);
        $this->assertSame(Log::class, (string) $stream->type());
        $this->assertSame('0123456789', (string) $this->stream->join(''));
        $this->assertSame('02468', (string) $stream->join(''));
    }

    public function testForeach()
    {
        $count = 0;
        $return = $this->stream->foreach(function(Log $log) use (&$count) {
            $this->assertSame((string) $count, (string) $log);
            ++$count;
        });

        $this->assertSame(10, $count);
    }

    public function testLoadingAllStreamDoesntAffectCursor()
    {
        $this->stream->next();
        $this->stream->size(); //load the whole stream
        $this->assertSame(1, $this->stream->key());
    }

    public function testGroupBy()
    {
        $groups = $this->stream->groupBy(function(Log $log): int {
            return (int) (string) $log % 3;
        });

        $this->assertInstanceOf(MapInterface::class, $groups);
        $this->assertSame('int', (string) $groups->keyType());
        $this->assertSame(StreamInterface::class, (string) $groups->valueType());
        $this->assertTrue($groups->contains(0));
        $this->assertTrue($groups->contains(1));
        $this->assertTrue($groups->contains(2));
        $this->assertCount(4, $groups->get(0));
        $this->assertCount(3, $groups->get(1));
        $this->assertCount(3, $groups->get(2));
    }

    public function testFirst()
    {
        $this->assertSame('0', (string) $this->stream->first());
    }

    public function testLast()
    {
        $this->assertSame('9', (string) $this->stream->last());
    }

    public function testLog()
    {
        //file rewalked every time
        $this->assertFalse($this->stream->contains($this->stream->get(2)));
        $this->assertFalse($this->stream->contains(new Log(
            new Now,
            new Str(''),
            new Map('string', Attribute::class)
        )));
    }

    /**
     * @expectedException Innmind\Immutable\Exception\ElementNotFoundException
     */
    public function testIndexOf()
    {
        //throws because the file is rewalked each time
        $this->stream->indexOf($this->stream->get(2));
    }

    public function testIndices()
    {
        $indices = $this->stream->indices();

        $this->assertInstanceOf(StreamInterface::class, $indices);
        $this->assertSame('int', (string) $indices->type());
        $this->assertSame(range(0, 9), $indices->toPrimitive());
    }

    public function testMap()
    {
        $stream = $this->stream->map(function(Log $log) {
            return clone $log;
        });

        $this->assertInstanceOf(StreamInterface::class, $stream);
        $this->assertSame(Log::class, (string) $stream->type());
        $this->assertFalse($this->stream->equals($stream));
    }

    public function testParition()
    {
        $partitions = $this->stream->partition(function(Log $log) {
            return (int) (string) $log % 2 === 0;
        });

        $this->assertInstanceOf(MapInterface::class, $partitions);
        $this->assertSame('bool', (string) $partitions->keyType());
        $this->assertSame(StreamInterface::class, (string) $partitions->valueType());
        $this->assertCount(5, $partitions->get(true));
        $this->assertCount(5, $partitions->get(false));
    }

    public function testSlice()
    {
        $slice = $this->stream->slice(0, 2);

        $this->assertInstanceOf(StreamInterface::class, $slice);
        $this->assertSame(Log::class, (string) $slice->type());
        $this->assertFalse($this->stream->equals($slice));
        $this->assertSame('01', (string) $slice->join(''));
    }

    public function testSplitAt()
    {
        $splits = $this->stream->splitAt(2);

        $this->assertInstanceOf(StreamInterface::class, $splits);
        $this->assertSame(StreamInterface::class, (string) $splits->type());
        $this->assertSame(Log::class, (string) $splits->first()->type());
        $this->assertSame(Log::class, (string) $splits->last()->type());
        $this->assertCount(2, $splits->first());
        $this->assertCount(8, $splits->last());
    }

    public function testTake()
    {
        $stream = $this->stream->take(3);

        $this->assertInstanceOf(StreamInterface::class, $stream);
        $this->assertSame(Log::class, (string) $stream->type());
        $this->assertCount(3, $stream);
        $this->assertSame('012', (string) $stream->join(''));
    }

    public function testTakeEnd()
    {
        $stream = $this->stream->takeEnd(3);

        $this->assertInstanceOf(StreamInterface::class, $stream);
        $this->assertSame(Log::class, (string) $stream->type());
        $this->assertCount(3, $stream);
        $this->assertSame('789', (string) $stream->join(''));
    }

    public function testAppend()
    {
        $stream = $this->stream->append($this->stream);

        $this->assertInstanceOf(StreamInterface::class, $stream);
        $this->assertSame(Log::class, (string) $stream->type());
        $this->assertSame('0123456789', (string) $this->stream->join(''));
        $this->assertSame('01234567890123456789', (string) $stream->join(''));
    }

    public function testIntersect()
    {
        $this->assertCount(0, $this->stream->intersect($this->stream->clear()));
        $this->assertSame(
            $this->stream->intersect($this->stream)->size(),
            $this->stream->size()
        );
    }

    public function testJoin()
    {
        $this->assertSame(
            '0|1|2|3|4|5|6|7|8|9',
            (string) $this->stream->join('|')
        );
    }

    public function testAdd()
    {
        $stream = $this->stream->add($this->stream->first());

        $this->assertInstanceOf(StreamInterface::class, $stream);
        $this->assertSame(Log::class, (string) $stream->type());
        $this->assertSame('01234567890', (string) $stream->join(''));
    }

    public function testSort()
    {
        $stream = $this->stream->sort(function(Log $a, Log $b) {
            return $a < $b;
        });

        $this->assertInstanceOf(StreamInterface::class, $stream);
        $this->assertSame(Log::class, (string) $stream->type());
        $this->assertSame('9876543210', (string) $stream->join(''));
    }

    public function testReduce()
    {
        $value = $this->stream->reduce(
            0,
            function(int $carry, Log $log): int {
                return $carry + (int) (string) $log;
            }
        );

        $this->assertSame(45, $value);
    }

    public function testClear()
    {
        $stream = $this->stream->clear();

        $this->assertInstanceOf(StreamInterface::class, $stream);
        $this->assertSame(Log::class, (string) $stream->type());
        $this->assertCount(10, $this->stream);
        $this->assertCount(0, $stream);
    }

    public function testReverse()
    {
        $stream = $this->stream->reverse();

        $this->assertInstanceOf(StreamInterface::class, $stream);
        $this->assertSame(Log::class, (string) $stream->type());
        $this->assertSame('0123456789', (string) $this->stream->join(''));
        $this->assertSame('9876543210', (string) $stream->join(''));
    }

    public function testSize()
    {
        $this->assertSame(10, $this->stream->size());
    }

    public function testCount()
    {
        $this->assertCount(10, $this->stream);
    }

    public function testPrimitive()
    {
        //file rewalked every time
        $this->assertNotSame(
            [
                $this->stream->get(0),
                $this->stream->get(1),
                $this->stream->get(2),
                $this->stream->get(3),
                $this->stream->get(4),
                $this->stream->get(5),
                $this->stream->get(6),
                $this->stream->get(7),
                $this->stream->get(8),
                $this->stream->get(9),
            ],
            $this->stream->toPrimitive()
        );
    }

    public function testArrayAccess()
    {
        $this->assertTrue(isset($this->stream[2]));
        //file rewalked each time
        $this->assertNotSame($this->stream->get(2), $this->stream[2]);
    }

    /**
     * @expectedException Innmind\Immutable\Exception\LogicException
     * @expectedExceptionMessage You can't modify a stream
     */
    public function testThrowWhenSettingOnStream()
    {
        $this->stream[] = $this->stream->first();
    }

    /**
     * @expectedException Innmind\Immutable\Exception\LogicException
     * @expectedExceptionMessage You can't modify a stream
     */
    public function testThrowWhenUnsettingOnStream()
    {
        unset($this->stream[0]);
    }
}
