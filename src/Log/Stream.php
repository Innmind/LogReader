<?php
declare(strict_types = 1);

namespace Innmind\LogReader\Log;

use Innmind\LogReader\Log;
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
    private $generator;
    private $type;
    private $logs;
    private $cursor = 0;

    public function __construct(\Generator $generator)
    {
        $this->generator = $generator;
        $this->type = new Str(Log::class);
        $this->logs = (new GenericStream(Log::class))
            ->add($generator->current());
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
        if ($this->logs->contains($index)) {
            return $this->logs->get($index);
        }

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
        if ($this->loaded()) {
            return $this->logs->indices();
        }

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
        $this->sync();

        return $this->logs->get($this->cursor);
    }

    public function key(): int
    {
        $this->sync();

        return $this->cursor;
    }

    public function next(): void
    {
        ++$this->cursor;
    }

    public function rewind(): void
    {
        $this->cursor = 0;
    }

    public function valid(): bool
    {
        if ($this->loaded()) {
            return $this->logs->offsetExists($this->cursor);
        }

        $this->sync();

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
        if ($this->loaded()) {
            return $this->logs;
        }

        $cursor = $this->cursor;

        while (!$this->loaded()) {
            $this->next();
            $this->sync();
        }

        $this->cursor = $cursor;

        return $this->logs;
    }

    private function loaded(): bool
    {
        return !$this->generator->valid();
    }

    private function synced(): bool
    {
        if ($this->loaded()) {
            return true;
        }

        try {
            $this->logs->get($this->cursor);

            return true;
        } catch (OutOfBoundException $e) {
            return false;
        }
    }

    private function sync(): void
    {
        if ($this->synced()) {
            return;
        }

        while (!$this->synced()) {
            $this->generator->next();

            if ($this->generator->valid()) {
                $this->logs = $this->logs->add($this->generator->current());
            }
        }
    }
}
