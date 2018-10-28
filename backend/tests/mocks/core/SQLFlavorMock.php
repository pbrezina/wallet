<?php declare(strict_types=1);

    namespace Shockie\Tests\Mocks\Core;

    use Shockie\Core\SQLDatabase;
    use Shockie\Core\SQLResult;
    use Shockie\Interfaces\ISQLFlavor;
    use Shockie\Tests\Mocks\Core\SQLResult_BaseClass;

    final class SQLFlavorMock extends SQLDatabase implements ISQLFlavor
    {
        protected function quoteObject(string $name) : string
        {
            return sprintf('`%s`', $name);
        }

        public function lastId(string $name = null) : int
        {
            return 0;
        }

        public function startTransaction() : void
        {
            return;
        }

        public function commit() : void
        {
            return;
        }

        public function rollback() : void
        {
            return;
        }

        /**
         * @return null -> NULL
         * @return string -> 'string'
         * @return other -> unchanged
         */
        public function value($value) : string
        {
            if ($value === null) {
                return 'NULL';
            }

            if (is_string($value)) {
                return sprintf("'%s'", $value);
            }

            return (string)$value;
        }

        protected function executeQuery(string $raw, string $prepared) : SQLResult
        {
            return new SQLResult_BaseClass();
        }
    }
?>
