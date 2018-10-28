<?php declare(strict_types=1);

    namespace Shockie\Tests\Mocks\Core;

    use ArrayIterator;
    use Traversable;
    use Shockie\Core\SQLResult;
    use Shockie\Interfaces\ISQLFlavor;

    final class SQLResult_BaseClass extends SQLResult
    {
        public function fetch()
        {
            return;
        }

        public function fetchAll() : array
        {
            return [];
        }

        public function asArray() : parent
        {
            return $this;
        }

        public function asMap() : parent
        {
            return $this;
        }

        public function asObject() : parent
        {
            return $this;
        }

        public function getIterator() : Traversable
        {
            return new ArrayIterator($this);
        }
    }
?>
