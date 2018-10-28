<?php declare(strict_types=1);

    namespace Shockie\Interfaces;

    use Shockie\Core\SQLResult;

    interface ISQLFlavor
    {
        /**
         * Return SQL flavor name.
         *
         * @return string
         */
        public function getFlavor() : string;

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
        public function database(string $db) : string;

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
        public function table(string $table) : string;

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
        public function column(string $column) : string;

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
        public function value($value) : string;

        /**
         * Execute an SQL query.
         *
         * @throws Shockie\Exceptions\SQLQueryException;
         *
         * @param string $query
         * @return SQLResult
         */
        public function run(string $query) : SQLResult;

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
        public function prepareQueryColumns(string $query) : string;

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
        public function prepareQueryValues(string $query, array $params = []) : string;

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
        public function prepareQueryString(string $query, array $params = []) : string;
    }
?>
