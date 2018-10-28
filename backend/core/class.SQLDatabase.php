<?php declare(strict_types=1);

    namespace Shockie\Core;

    use InvalidArgumentException;
    use LogicException;
    use Shockie\Core\SQLDelete;
    use Shockie\Core\SQLExpression;
    use Shockie\Core\SQLInnerJoin;
    use Shockie\Core\SQLLeftOuterJoin;
    use Shockie\Core\SQLRightOuterJoin;
    use Shockie\Core\SQLSelect;
    use Shockie\Core\SQLUpdate;
    use Shockie\Core\SQLInsert;
    use Shockie\Core\SQLResult;
    use Shockie\Core\SQLPreparedQuery;
    use Shockie\Interfaces\ISQLFlavor;

    /**
     * Base class for SQL databases.
     *
     * Provides abstraction around arbitrary SQL database.
     */
    abstract class SQLDatabase implements ISQLFlavor
    {
        private $table_prefix;

        public function __construct()
        {
            $this->table_prefix = null;
        }

        /**
         * Create and SQL expression object.
         *
         * @param expression See SQLExpression::custom() for format.
         * @param arguments See SQLExpression::custom() for format.
         * @return SQLExpression
         *
         * If $expression is NULL then a fresh SQLExpression object is returned.
         */
        public function expression(?string $expression = null,
                                   array $arguments = []) : SQLExpression
        {
            $obj = new SQLExpression($this);

            if ($expression !== null) {
                $obj->custom($expression, $arguments);
            }

            return $obj;
        }

        /**
         * Create new SQL Inner Join object.
         *
         * @param left See SQLInnerJoin::__consruct() for syntax.
         * @param right See SQLInnerJoin::__consruct() for syntax.
         * @return SQLInnerJoin
         */
        public function innerJoin($left, $right) : SQLInnerJoin
        {
            return new SQLInnerJoin($this, $left, $right);
        }

        /**
         * Create new SQL Left Outer Join object.
         *
         * @param left See SQLLeftOuterJoin::__consruct() for syntax.
         * @param right See SQLLeftOuterJoin::__consruct() for syntax.
         * @return SQLLeftOuterJoin
         */
        public function leftOuterJoin($left, $right) : SQLLeftOuterJoin
        {
            return new SQLLeftOuterJoin($this, $left, $right);
        }

        /**
         * Create new SQL Right Outer Join object.
         *
         * @param left See SQLRightOuterJoin::__consruct() for syntax.
         * @param right See SQLRightOuterJoin::__consruct() for syntax.
         * @return SQLRightOuterJoin
         */
        public function rightOuterJoin($left, $right) : SQLRightOuterJoin
        {
            return new SQLRightOuterJoin($this, $left, $right);
        }

        /**
         * Create new SQL Select object and select specified columns.
         *
         * @param args See SQLSelect::columns() for format.
         * @return SQLSelect
         */
        public function select(...$args) : SQLSelect
        {
            $obj = new SQLSelect($this);
            return $obj->columns(...$args);
        }

        /**
         * Create new SQL Insert object.
         *
         * @param table Table to insert data into.
         * @return SQLInsert
         */
        public function insertInto($table) : SQLInsert
        {
            return new SQLInsert($this, $table);
        }

        /** Create new SQL Update object.
         *
         * @param table Table that should be updated.
         * @param unconditional If false, exception is thrown from self->get() if there is no WHERE condition set.
         * @return SQLUpdate
         */
        public function update(string $table, bool $unconditional = false) : SQLUpdate
        {
            return new SQLUpdate($this, $table, $unconditional);
        }

        /** Create new SQL Delete object.
         *
         * @param table Table to delete rows from.
         * @param unconditional If false, exception is thrown from self->get() if there is no WHERE condition set.
         * @return SQLDelete
         */
        public function delete(string $table, bool $unconditional = false) : SQLDelete
        {
            return new SQLDelete($this, $table, $unconditional);
        }

        /**
         * Select all columns from $table and filter them by $id.
         *
         * @param table Table to select from.
         * @param id    Value that is used to filter rows.
         * @param column Column name that should be used in filter.
         * @return SQLSelect
         *
         * Final query looks like:
         *   SELECT * FROM $table WHERE $column = $id LIMIT 1
         *
         * @example
         *   $query = $db->selectById('users', 1);
         *   $query->run();
         */
        public function selectById(string $table, $id, $column = 'id') : SQLSelect
        {
            return $this->select('*')->from($table)->where($column, '=', $id)->limit(1);
        }

        /**
         * Update rows that match filter by $id in $table.
         *
         * @param table Table that should be updated.
         * @param id    Value that is used to filter rows.
         * @param column Column name that should be used in filter.
         * @return SQLUpdate
         *
         * Final query looks like:
         *   UPDATE $table (SET must be set) WHERE $column = $id
         *
         * SET statement must be set on the object before execution.
         *
         * @example
         *   $query = $db->updateById('users', 1)->set(['name' => 'John]);
         *   $query->run();
         */
        public function updateById(string $table, $id, $column = 'id') : SQLUpdate
        {
            return $this->update($table)->where($column, '=', $id);
        }

        /**
         * Delete rows that match filter by $id from $table.
         *
         * @param table Table to delete from.
         * @param id    Value that is used to filter rows.
         * @param column Column name that should be used in filter.
         * @return SQLDelete
         *
         * Final query looks like:
         *   DELETE FROM $table WHERE $column = $id
         *
         * @example
         *   $query = $db->deleteById('users', 1);
         *   $query->run();
         */
        public function deleteById(string $table, $id, $column = 'id') : SQLDelete
        {
            return $this->delete($table)->where($column, '=', $id);
        }

        /**
         * Return configured table prefix or null if there is none.
         *
         * @return string|null
         */
        public function getTablePrefix() : ?string
        {
            return $this->table_prefix;
        }

        /**
         * Set table prefix.
         *
         * Table prefix is automatically added to the table name when
         * self::table() is called.
         *
         * @param string|null $prefix If null then the prefix is removed.
         * @return void
         */
        public function setTablePrefix(?string $prefix) : void
        {
            $this->table_prefix = $prefix;
        }

        /**
         * Prepend table prefix to given table name.
         *
         * @example
         *   $db->setTablePrefix('my_');
         *   $table = $db->table('db.table'); // -> `db`.`my_table`
         *
         * @param string $table
         * @return string
         */
        public function applyTablePrefix(string $table) : string
        {
            return $this->table_prefix . $table;
        }

        /**
         * Transform database name per database driver rules.
         *
         * For example encapsulating it in quotes or backticks.
         * The precise method depends on the underlying database driver.
         *
         * @example db -> `db`
         *
         * @throws \InvalidArgumentException If database name is empty.
         *
         * @param string $db
         * @return string
         */
        public function database(string $db) : string
        {
            if (empty($db)) {
                throw new InvalidArgumentException(
                    'Database name can not be empty.'
                );
            }

            return $this->quoteObject($db);
        }

        /**
         * Transform table name per database driver rules.
         *
         * It will parse the table name into database (if present) and table
         * parts and transform it per driver rules, for example encapsulating
         * it in quotes or backticks. The precise method depends on the
         * underlying database driver.
         *
         * @example table -> `table`
         * @example db.table -> `db`.`table`
         *
         * @throws \InvalidArgumentException If table name is empty.
         *
         * @param string $table
         * @return string
         */
        public function table(string $table) : string
        {
            $parts = $this->parseObjectName($table);
            $name = $parts['name'];
            $left = $parts['left'];

            if (empty($name)) {
                throw new InvalidArgumentException(
                    'Table name can not be empty.'
                );
            }

            if (!empty($left)) {
                $left = $this->database($left) . '.';
            }

            $name = $this->applyTablePrefix($name);
            return sprintf('%s%s', $left, $this->quoteObject($name));
        }

        /**
         * Transform column name per database driver rules.
         *
         * It will parse the column name into database (if present),
         * table (if present) and column parts and transform it per driver
         * rules, for example encapsulating it in quotes or backticks.
         * The precise method depends on the underlying database driver.
         *
         * @example column -> `column`
         * @example table.column -> `table`.`column`
         * @example db.table.column -> `db`.`table`.`column`
         *
         * @throws \InvalidArgumentException If column name is empty.
         *
         * @param string $column
         * @return string
         */
        public function column(string $column) : string
        {
            $parts = $this->parseObjectName($column);
            $name = $parts['name'];
            $left = $parts['left'];

            if (empty($name)) {
                throw new InvalidArgumentException(
                    'Column name can not be empty.'
                );
            }

            if (!empty($left)) {
                $left = $this->table($left) . '.';
            }

            return sprintf('%s%s', $left, $this->quoteObject($name));
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
         * Quote object name per database driver rules.
         *
         * @throws \InvalidArgumentException If object name is empty.
         *
         * @param string $name
         * @return string
         */
        abstract protected function quoteObject(string $name) : string;

        /**
         * Return last automatically generated identificator.
         *
         * @param string $name Name of the sequence object if any.
         * @return integer
         */
        abstract public function lastId(string $name = null) : int;

        /**
         * Start transaction.
         *
         * Transactions can be nested in general, however it is guaranteed
         * that only the first level transaction will be commited or cancelled.
         *
         * Calling commit() or rollback() on a nested transaction will only
         * remember the action internally but no action on the server is done.
         * Only commiting or cancelling the first transaction will perform
         * commit or rollback operation on the server.
         *
         * @throws Shockie\Exceptions\SQLTransactionException
         *
         * @return void
         */
        abstract public function startTransaction() : void;

        /**
         * Commit transaction.
         *
         * @throws Shockie\Exceptions\SQLTransactionException
         *
         * @return void
         */
        abstract public function commit() : void;

        /**
         * Rollback transaction.
         *
         * @throws Shockie\Exceptions\SQLTransactionException
         *
         * @return void
         */
        abstract public function rollback() : void;

        /**
         * Convert PHP typed value into a string to be used in database query.
         *
         * Exact conversion depends on the underlying database driver.
         *
         * @example null -> NULL
         * @example string -> 'string'
         * @example other -> unchanged
         *
         * @param mixed $value
         * @return string
         */
        abstract public function value($value) : string;

        /**
         * Prepare SQL query for multiple executions.
         *
         * @param string $query SQL Query.
         * @return SQLPreparedQuery
         */
        public function prepare(string $query) : SQLPreparedQuery
        {
            return new SQLPreparedQuery($this, $query);
        }

        /**
         * Execute a single SQL query.
         *
         * SQL Query can contain several parametrized arguments.
         *
         * @see SQLDatabase::prepareQueryString() for the format.
         *
         * @param string $query SQLquery to execture.
         * @param array $params Array of parameter values in 'key' => 'value'
         *              pairs that will be replaced in the query.
         *
         * @throws Shockie\Exceptions\SQLQueryException;
         *
         * @param string $query
         * @return SQLResult
         */
        public function run(string $query, array $params = []) : SQLResult
        {
            $prepared = $this->prepareQueryString($query, $params);

            return $this->executeQuery($query, $prepared);
        }

        /**
         * Exectude query on specific database driver.
         *
         * @param string $raw Unprepared query string that should be used
         *                    in debug messages and exceptions.
         * @param string $prepared Prepared query string to be executed. Since
         *                         this may contain sensitive user data it
         *                         should not be visible in any logs.
         *
         * @throws Shockie\Exceptions\SQLQueryException;
         *
         * @return SQLResult
         */
        abstract protected function executeQuery(string $raw, string $prepared) : SQLResult;

        /**
         * Replace parametrized column names with their escaped name.
         *
         * @param string $query SQL expression.
         * @return string Result SQL query string.
         *
         * $query is an arbitrary SQL query which in addition can contain:
         *
         *   - ::column
         *     - 'column' will be escaped as column name per database rules
         *
         *   - ::table.column
         *     - 'table' will be escaped as table name per database rules
         *     - 'column' will be escaped as column name per database rules
         *
         *   - ::database.table.column
         *     - 'database' will be escaped as database name per database rules
         *     - 'table' will be escaped as table name per database rules
         *     - 'column' will be escaped as column name per database rules
         *
         * @example Filter rows by id.
         *   prepareQueryString('SELECT ::id FROM table');
         */
        public function prepareQueryColumns(string $query) : string
        {
            /* Explode query by quote to find strings.
             * SELECT * FROM example WHERY x = "1" AND y = "2" ANd z = 3
             * ->
             * array (size=5)
             *   0 => string 'SELECT * FROM example WHERY x = ' (length=32)
             *   1 => string '1' (length=1)
             *   2 => string ' AND y = ' (length=9)
             *   3 => string '2' (length=1)
             *   4 => string ' ANd z = 3' (length=10)
             *
             * Every odd index is a string value and we should not touch it.
             */
            $parts = explode("'", $query);

            foreach ($parts as $index => $value) {
                if ($index & 1) {
                    continue;
                }

                /* Process column names. */
                $value = preg_replace_callback(
                    '/\B::([\w._]+)/',
                    function ($matches) {
                        return $this->column($matches[1]);
                    },
                    $value
                );

                $parts[$index] = $value;
            }

            return implode("'", $parts);
        }

        /**
         * Replace parametrized arguments with actual value.
         *
         * @param string $query SQL expression.
         * @param array $params Query parameters in 'key' => 'value' pairs.
         * @return string Result SQL query string.
         *
         * $query is an arbitrary SQL query which in addition can contain:
         *
         *   - :argument
         *     - 'argument' key will be looked up in $params and replaced
         *       with its value. This value will be escaped per database rules.
         *
         * @throws LogicExpression If an parameter is present in the query but is not set.
         *
         * @example Filter rows by id.
         *   prepareQueryValues(
         *     'SELECT * FROM table WHERE id = :id',
         *     ['id' => 5]
         *   );
         *
         *   prepareQueryValues(
         *     'SELECT * FROM table WHERE id = :0',
         *     [5]
         *   );
         */
        public function prepareQueryValues(string $query, array $params = []) : string
        {
            /* Explode query by quote to find strings.
             * SELECT * FROM example WHERY x = "1" AND y = "2" ANd z = 3
             * ->
             * array (size=5)
             *   0 => string 'SELECT * FROM example WHERY x = ' (length=32)
             *   1 => string '1' (length=1)
             *   2 => string ' AND y = ' (length=9)
             *   3 => string '2' (length=1)
             *   4 => string ' ANd z = 3' (length=10)
             *
             * Every odd index is a string value and we should not touch it.
             */
            $parts = explode("'", $query);

            foreach ($parts as $index => $value) {
                if ($index & 1) {
                    continue;
                }

                /* Process column names. */
                $value = preg_replace_callback(
                    '/\B::([\w._]+)/',
                    function ($matches) {
                        return $this->column($matches[1]);
                    },
                    $value
                );

                /* Process arguments. */
                $value = preg_replace_callback(
                    '/(?<!:)\B:([\w._]+)/',
                    function ($matches) use ($params) {
                        $key = $matches[1];
                        if (!array_key_exists($key, $params)) {
                            throw new LogicException(
                                'Key "' . $key . '" must be set.'
                            );
                        }

                        return $this->value($params[$key]);
                    },
                    $value
                );

                $parts[$index] = $value;
            }

            return implode("'", $parts);
        }

        /**
         * Replace parametrized arguments and column names with actual value.
         *
         * @param string $query SQL expression.
         * @param array $params Query parameters in 'key' => 'value' pairs.
         * @return string Result SQL query string.
         *
         * $query is an arbitrary SQL query which in addition can contain:
         *
         *   - ::column
         *     - 'column' will be escaped as column name per database rules
         *
         *   - ::table.column
         *     - 'table' will be escaped as table name per database rules
         *     - 'column' will be escaped as column name per database rules
         *
         *   - ::database.table.column
         *     - 'database' will be escaped as database name per database rules
         *     - 'table' will be escaped as table name per database rules
         *     - 'column' will be escaped as column name per database rules
         *
         *   - :argument
         *     - 'argument' key will be looked up in $params and replaced
         *       with its value. This value will be escaped per database rules.
         *
         * @throws LogicExpression If an parameter is present in the query but is not set.
         *
         * @example Filter rows by id.
         *   prepareQueryString(
         *     'SELECT * FROM table WHERE ::id = :id',
         *     ['id' => 5]
         *   );
         *
         *   prepareQueryString(
         *     'SELECT * FROM table WHERE ::id = :0',
         *     [5]
         *   );
         */
        public function prepareQueryString(string $query, array $params = []) : string
        {
            return $this->prepareQueryValues(
                $this->prepareQueryColumns($query),
                $params
            );
        }
    }
?>
