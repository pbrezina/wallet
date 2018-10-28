<?php declare(strict_types=1);

    namespace Shockie\Core;

    use Traversable;
    use IteratorAggregate;

    /**
     * SQL Result Base class.
     *
     * Extend this class to create SQL result accessor.
     */
    abstract class SQLResult implements IteratorAggregate
    {
        abstract public function fetch();
        abstract public function fetchAll() : array;
        abstract public function asArray() : self;
        abstract public function asMap() : self;
        abstract public function asObject() : self;
        abstract public function getIterator() : Traversable;
    }
?>
