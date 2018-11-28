<?php
declare(strict_types = 1);

namespace Innmind\LogReader\Log;

use Innmind\LogReader\Log;
use Innmind\Stream\Readable;
use Innmind\Immutable\{
    StreamInterface,
    Stream as GenericStream,
    MapInterface,
    Str,
    Exception\OutOfBoundException,
    Exception\LogicException
};

final class Stream implements StreamInterface
{
    private $walk;
    private $file;
    private $generator;
    private $type;
    private $cursor = 0;

    public function __construct(callable $walker, Readable $file)
    {
        $this->walk = $walker;
        $this->file = $file;
        $this->type = new Str(Log::class);
        $this->rewind();
    }

    /**
     * {@inheritdoc}
     */
    public function type(): Str
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function get(int $index): Log
    {
        $this->rewind();

        while ($this->valid()) {
            if ($this->key() === $index) {
                return $this->current();
            }

            $this->next();
        }

        throw new OutOfBoundException;
    }

    /**
     * {@inheritdoc}
     */
    public function diff(StreamInterface $stream): StreamInterface
    {
        return $this->logs()->diff($stream);
    }

    /**
     * {@inheritdoc}
     */
    public function distinct(): StreamInterface
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function drop(int $size): StreamInterface
    {
        return $this->logs()->drop($size);
    }

    /**
     * {@inheritdoc}
     */
    public function dropEnd(int $size): StreamInterface
    {
        return $this->logs()->dropEnd($size);
    }

    /**
     * {@inheritdoc}
     */
    public function equals(StreamInterface $stream): bool
    {
        return $this->logs()->equals($stream);
    }

    /**
     * {@inheritdoc}
     */
    public function filter(callable $predicate): StreamInterface
    {
        $logs = new GenericStream(Log::class);
        $this->rewind();

        while ($this->valid()) {
            $log = $this->current();

            if ($predicate($log) === true) {
                $logs = $logs->add($log);
            }

            $this->next();
        }

        return $logs;
    }

    /**
     * {@inheritdoc}
     */
    public function foreach(callable $function): StreamInterface
    {
        $this->rewind();

        while ($this->valid()) {
            $function($this->current());
            $this->next();
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function groupBy(callable $discriminator): MapInterface
    {
        return $this->logs()->groupBy($discriminator);
    }

    /**
     * {@inheritdoc}
     */
    public function first(): Log
    {
        return $this->get(0);
    }

    /**
     * {@inheritdoc}
     */
    public function last(): Log
    {
        return $this->get($this->size() - 1);
    }

    /**
     * {@inheritdoc}
     */
    public function contains($element): bool
    {
        return $this->logs()->contains($element);
    }

    /**
     * {@inheritdoc}
     */
    public function indexOf($element): int
    {
        return $this->logs()->indexOf($element);
    }

    /**
     * {@inheritdoc}
     */
    public function indices(): StreamInterface
    {
        $indices = new GenericStream('int');

        if ($this->size() === 0) {
            return $indices;
        }

        foreach (range(0, $this->size() - 1) as $index) {
            $indices = $indices->add($index);
        }

        return $indices;
    }

    /**
     * {@inheritdoc}
     */
    public function map(callable $function): StreamInterface
    {
        return $this->logs()->map($function);
    }

    /**
     * {@inheritdoc}
     */
    public function pad(int $size, $element): StreamInterface
    {
        return $this->logs()->pad($size, $element);
    }

    /**
     * {@inheritdoc}
     */
    public function partition(callable $predicate): MapInterface
    {
        return $this->logs()->partition($predicate);
    }

    /**
     * {@inheritdoc}
     */
    public function slice(int $from, int $until): StreamInterface
    {
        return $this->logs()->slice($from, $until);
    }

    /**
     * {@inheritdoc}
     */
    public function splitAt(int $position): StreamInterface
    {
        return $this->logs()->splitAt($position);
    }

    /**
     * {@inheritdoc}
     */
    public function take(int $size): StreamInterface
    {
        return $this->logs()->take($size);
    }

    /**
     * {@inheritdoc}
     */
    public function takeEnd(int $size): StreamInterface
    {
        return $this->logs()->takeEnd($size);
    }

    /**
     * {@inheritdoc}
     */
    public function append(StreamInterface $stream): StreamInterface
    {
        return $this->logs()->append($stream);
    }

    /**
     * {@inheritdoc}
     */
    public function intersect(StreamInterface $stream): StreamInterface
    {
        return $this->logs()->intersect($stream);
    }

    /**
     * {@inheritdoc}
     */
    public function join(string $separator): Str
    {
        return $this->logs()->join($separator);
    }

    /**
     * {@inheritdoc}
     */
    public function add($element): StreamInterface
    {
        return $this->logs()->add($element);
    }

    /**
     * {@inheritdoc}
     */
    public function sort(callable $function): StreamInterface
    {
        return $this->logs()->sort($function);
    }

    /**
     * {@inheritdoc}
     */
    public function reduce($carry, callable $reducer)
    {
        $this->rewind();

        while ($this->valid()) {
            $carry = $reducer($carry, $this->current());
            $this->next();
        }

        return $carry;
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): StreamInterface
    {
        return new GenericStream(Log::class);
    }

    /**
     * {@inheritdoc}
     */
    public function reverse(): StreamInterface
    {
        return $this->logs()->reverse();
    }

    public function size(): int
    {
        return $this->logs()->size();
    }

    public function count(): int
    {
        return $this->size();
    }

    public function toPrimitive(): array
    {
        return $this->logs()->toPrimitive();
    }

    public function current(): Log
    {
        return $this->generator->current();
    }

    public function key(): int
    {
        return $this->generator->key();
    }

    public function next(): void
    {
        $this->generator->next();
    }

    public function rewind(): void
    {
        $this->generator = ($this->walk)($this->file);
    }

    public function valid(): bool
    {
        return $this->generator->valid();
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($offset): bool
    {
        return $this->indices()->contains($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($offset): Log
    {
        return $this->get($offset);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($offset, $value): void
    {
        throw new LogicException('You can\'t modify a stream');
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($offset): void
    {
        throw new LogicException('You can\'t modify a stream');
    }

    private function logs(): StreamInterface
    {
        $logs = new GenericStream(Log::class);
        $generator = ($this->walk)($this->file);

        foreach ($generator as $log) {
            $logs = $logs->add($log);
        }

        return $logs;
    }
}
