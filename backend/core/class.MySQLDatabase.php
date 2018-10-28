<?php declare(strict_types=1);

    namespace Shockie\Core;

    use array_keys;
    use in_array;
    use InvalidArgumentException;
    use Shockie\Core\PDODatabase;
    use Shockie\Exceptions\InvalidFormatException;

    /**
     * Base class for SQL databases.
     *
     * Provides abstraction around arbitrary SQL database.
     */
    class MySQLDatabase extends PDODatabase
    {
        public function __construct(array $specification,
                                    ?string $username = null,
                                    ?string $password = null,
                                    array $options = [])
        {
            $dsn = $this->buildDSN($specification);

            parent::__construct($dsn, $username, $password, $options);
        }

        /**
         * Return 'mysql' as the flavor name.
         *
         * @return string
         */
        public function getFlavor() : string
        {
            return 'mysql';
        }

        private function validateSpecification(array $specification) : void
        {
            $keys = ['host', 'port', 'dbname', 'unix_socket', 'charset'];

            foreach (array_keys($specification) as $key) {
                if (!in_array($key, $keys)) {
                    throw new InvalidFormatException("Unknown property '$key'");
                }
            }
        }

        private function buildDSN(array $specification) : string
        {
            $this->validateSpecification($specification);

            $dsn = 'mysql:';

            foreach ($specification as $key => $value) {
                if ($value === null) {
                    continue;
                }

                $dsn .= sprintf('%s=%s;', $key, $value);
            }

            return $dsn;
        }

        private function parseObjectName(string $name)
        {
            $parts = ['left' => '', 'name' => ''];

            $dot = mb_strrchr($name, '.');
            if ($dot === false) {
                $parts['name'] = $name;
            } else {
                $parts['left'] = mb_substr($name, 0, -mb_strlen($dot));
                $parts['name'] = mb_substr($dot, 1);
            }

            return $parts;
        }

        /**
         * Encapsulate object name with backticks.
         *
         * @example database -> `database`
         * @example table -> `table`
         * @example column -> `column`
         *
         * @throws \InvalidArgumentException If object name is empty.
         *
         * @param string $db
         * @return string
         */
        protected function quoteObject(string $name) : string
        {
            if (empty($name)) {
                throw new InvalidArgumentException(
                    'Object name can not be empty.'
                );
            }

            return '`' . str_replace('`', '``', $name) . '`';
        }
    }
?>
