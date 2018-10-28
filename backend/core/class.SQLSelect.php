<?php declare(strict_types=1);

    namespace Shockie\Core;

    use InvalidArgumentException;
    use LogicException;
    use Shockie\Core\SQLExpression;
    use Shockie\Core\SQLQuery;
    use Shockie\Interfaces\ISQLFlavor;

    /**
     * SQL Select Query.
     *
     * Construct an SQL SELECT query formatted as:
     * SELECT column, ... FROM table, ...
     *   WHERE expression
     *   GROUP BY expression
     *   HAVING expression
     *   ORDER BY expression, ...
     *   LIMIT limit OFFSET offset
     */
    class SQLSelect extends SQLQuery
    {
        private $fields;
        private $from;
        private $where;
        private $groupby;
        private $having;
        private $orderby;
        private $limit;
        private $offset;

        /**
         * @param flavor Provides database-specific functionality.
         */
        public function __construct(ISQLFlavor $flavor)
        {
            parent::__construct($flavor);

            $this->fields = '';
            $this->from = '';
            $this->where = '';
            $this->groupby = '';
            $this->having = '';
            $this->orderby = '';
            $this->limit = null;
            $this->offset = null;
        }

        /**
         * Select all columns (SELECT *).
         *
         * @return self
         */
        public function all() : self
        {
            $this->fields .= ', *';

            return $this;
        }

        /**
         * Specify columns to be selected.
         *
         * @param args Variable number of arguments.
         * @return self
         *
         * If no arguments are given, all columns will be selected with '*' specifier.
         *
         * Possible formats of each argument
         *
         * - (string) '*'
         *   - select all columns
         *
         * - (string) 'column'
         *   - column name
         *
         * - (string) 'table.column'
         *   - column name from given table
         *
         * - (array) [specifier, ...] where specifier is one of:
         *   - (int) key, (string) 'column'
         *     - column name, key is integer (or not specified manually)
         *   - (int) key => (string) 'table.column'
         *     - column name from given table, key is integer (or not specified manually)
         *   - (string) 'alias' => (string) 'column'
         *     - aliased column name
         *   - (string) 'alias' => (string) 'table.column'
         *     - aliased column name from specific table
         *   - (string) 'alias' => (SQLExpression) expression
         *     - aliased expression
         */
        public function columns(...$args) : self
        {
            if (empty($args)) {
                $this->fields = '*';
                return $this;
            }

            foreach ($args as $arg) {
                if (!is_array($arg)) {
                    $this->addColumn($arg);
                    continue;
                }

                foreach ($arg as $alias => $column) {
                    if (is_int($alias)) {
                        $this->addColumn($column);
                        continue;
                    }

                    if ($column instanceof SQLExpression) {
                        if (empty($alias)) {
                            throw new InvalidArgumentException(
                                'You must specify an alias for expression.'
                            );
                        }

                        $this->addColumn($column->get(), $alias, false);
                        continue;
                    }

                    $this->addColumn($column, $alias);
                    continue;
                }
            }

            return $this;
        }

        /**
         * Specify tables to query.
         *
         * @param args Variable number of arguments.
         * @return self
         *
         * At least one table must be specified.
         *
         * Possible formats of each argument
         *
         * - (string) 'table'
         *   - table name
         *
         * - (SQLJoin) join
         *   - instance of SQLJoin
         *
         * - (array) [specifier, ...] where specifier is one of:
         *   - (int) key => (string) 'table'
         *     - table name, key is integer (or not specified manually)
         *   - (int) key => (SQLJoin) join
         *     - instance of SQLJoin, key is integer (or not specified manually)
         *   - (string) 'alias' => (string) 'table'
         *     - aliased table
         *   - (string) 'alias' => (SQLSelect) query
         *     - aliased SELECT query
         */
        public function from(...$args) : self
        {
            foreach ($args as $arg) {
                if (!is_array($arg)) {
                    if ($arg instanceof SQLJoin) {
                        $this->addTable($arg->get(), null, false);
                        continue;
                    }

                    $this->addTable($arg);
                    continue;
                }

                foreach ($arg as $alias => $value) {
                    if (is_int($alias)) {
                        if ($value instanceof SQLJoin) {
                            $this->addTable($value->get(), null, false);
                            continue;
                        }

                        $this->addTable($value);
                        continue;
                    }

                    if ($value instanceof SQLSelect) {
                        $this->addTable('(' . $value->get() . ')', $alias, false);
                        continue;
                    }

                    $this->addTable($value, $alias);
                    continue;
                }
            }

            return $this;
        }

        /**
         * Provide condition to filter rows.
         *
         * @param args See SQLExpression::Condition for format.
         * @return self
         */
        public function where(...$args) : self
        {
            $this->where = SQLExpression::Condition($this->flavor, ...$args);

            return $this;
        }

        /**
         * Specify grouping conditions.
         *
         * @param args Variable number of arguments.
         * @return self
         *
         * Possible formats of each argument
         *
         * - (string) 'column'
         *   - column name
         *
         * - (string) 'table.column'
         *   - column name from given table
         *
         * - (SQLExpression) expression
         *   - instance of SQLExpression
         */
        public function groupby(...$args) : self
        {
            foreach ($args as $arg) {
                if ($arg instanceof SQLExpression) {
                    $this->groupby .= ', ' . $arg->get();
                    continue;
                }

                $this->groupby .= ', ' . $this->flavor->column($arg);
            }

            return $this;
        }

        /**
         * Provide condition on aggregate functions that must be met.
         *
         * @param ...$args See SQLExpression::Condition for format.
         * @return self
         */
        public function having(...$args) : self
        {
            $this->having = SQLExpression::Condition($this->flavor, ...$args);

            return $this;
        }

        /**
         * Specify order conditions.
         *
         * @param args Variable number of arguments.
         * @return self
         *
         * Possible formats of each argument
         *
         * - (string) 'column'
         *   - column name, ascending order is assumed
         *
         * - (string) 'table.column'
         *   - column name from given table, ascending order is assumed
         *
         * - (SQLExpression) expression
         *   - instance of SQLExpression, ascending order is assumed
         *
         * - (array) [by, how] where
         *   - by is (string) 'column), (string) 'table.column' or instance of SQLExpression
         *   - how is either 'ASC' or 'DESC' representing ascending or descending order
         */
        public function orderby(...$args) : self
        {
            foreach ($args as $arg) {
                if (!is_array($arg)) {
                    if ($arg instanceof SQLExpression) {
                        $this->addOrderBy($arg->get(), 'ASC', false);
                        continue;
                    }

                    $this->addOrderBy($arg, 'ASC');
                    continue;
                }

                if (count($arg) != 2) {
                    throw new InvalidArgumentException(
                        'Unexcepted number of array elements (two required).'
                    );
                }

                $field = $arg[0];
                $order = strtoupper($arg[1]);

                if (!in_array($order, ['ASC', 'DESC'])) {
                    throw new InvalidArgumentException(
                        'Unexpected order value, got [' . $order . '] expected "ASC" or "DESC"'
                    );
                }

                if ($field instanceof SQLExpression) {
                    $this->addOrderBy($field->get(), $order, false);
                    continue;
                }

                $this->addOrderBy($field, $order);
            }

            return $this;
        }

        /**
         * Limit number of returned rows.
         *
         * @param limit Maximum number of rows to return.
         * @param offset Number of rows to skip.
         * @return self
         */
        public function limit(int $limit, ?int $offset = null) : self
        {
            $this->limit = $limit;
            $this->offset = $offset;

            return $this;
        }

        /**
         * Return constructed SELECT query.
         */
        public function get() : string
        {
            if (empty($this->from)) {
                throw new LogicException('From clause must be set.');
            }

            $fields = empty($this->fields) ? '*' : ltrim($this->fields, ', ');

            $query  =                                'SELECT '   . $fields;
            $query .=                               ' FROM '     . ltrim($this->from, ', ');
            $query .= empty($this->where)    ? '' : ' WHERE '    . $this->where;
            $query .= empty($this->groupby)  ? '' : ' GROUP BY ' . ltrim($this->groupby, ', ');
            $query .= empty($this->having)   ? '' : ' HAVING '   . $this->having;
            $query .= empty($this->orderby)  ? '' : ' ORDER BY ' . ltrim($this->orderby, ', ');
            $query .= $this->limit === null  ? '' : ' LIMIT '    . $this->limit;
            $query .= $this->offset === null ? '' : ' OFFSET '   . $this->offset;

            return $query;
        }

        private function addColumn(string $column,
                                   ?string $alias = null,
                                   bool $escape_column = true) : void
        {
            if ($column == '*') {
                $this->fields .= '*';
                return;
            }

            if ($alias === null) {
                $this->fields .= sprintf(
                    ', %s',
                    $escape_column ? $this->flavor->column($column) : $column
                );

                return;
            }

            $this->fields .= sprintf(
                ', %s AS %s',
                $escape_column ? $this->flavor->column($column) : $column,
                $this->flavor->column($alias)
            );
        }

        private function addTable(string $table,
                                  ?string $alias = null,
                                  bool $escape_table = true) : void
        {
            if ($alias === null) {
                $this->from .= sprintf(
                    ', %s',
                    $escape_table ? $this->flavor->table($table) : $table
                );

                return;
            }

            $this->from .= sprintf(
                ', %s AS %s',
                $escape_table ? $this->flavor->table($table) : $table,
                $this->flavor->column($alias)
            );
        }

        private function addOrderBy(string $column,
                                    string $order,
                                    bool $escape = true) : void
        {
            $this->orderby .= sprintf(
                ', %s %s',
                $escape ? $this->flavor->column($column) : $column,
                $order
            );
        }
    }
?>
