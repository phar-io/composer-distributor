<?php

declare(strict_types=1);

namespace PharIo\ComposerDistributor;

use function current;
use function key;
use function next;
use function reset;

trait IteratorImplementation
{
    abstract public function &getList() : array;

    #[\ReturnTypeWillChange]
    public function current()
    {
        return current($this->getList());
    }

    #[\ReturnTypeWillChange]
    public function next()
    {
        next($this->getList());
    }

    #[\ReturnTypeWillChange]
    public function key()
    {
        return key($this->getList());
    }

    #[\ReturnTypeWillChange]
    public function valid()
    {
        return false === key($this->getList());
    }

    #[\ReturnTypeWillChange]
    public function rewind()
    {
        return reset($this->getList());
    }
}
