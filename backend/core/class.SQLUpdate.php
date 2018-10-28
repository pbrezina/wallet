<?php declare(strict_types=1);

    namespace Shockie\Core;

    use InvalidArgumentException;
    use LogicException;
    use Shockie\Core\SQLExpression;
    use Shockie\Core\SQLQuery;
    use Shockie\Interfaces\ISQLFlavor;

    /**
     * SQL Update Query.
     *
     * Construct an SQL UPDATE query formatted as:
     * UPDATE table SET column = value, ... WHERE expression
     */
    class SQLUpdate extends SQLQuery
    {
        private $table;
        private $values;
        private $where;
        private $unconditional;

        /**
         * @param flavor Provides database-specific functionality.
         * @param table Table that should be updated.
         * @param unconditional If false, exception is thrown from self->get() if there is no WHERE condition set.
         */
        public function __construct(ISQLFlavor $flavor,
                                    string $table,
                                    bool $unconditional = false)
        {
            parent::__construct($flavor);

            if (empty($table)) {
                throw new InvalidArgumentException('Table name must not be empty.');
            }

            $this->table = $table;
            $this->values = '';
            $this->where = '';
            $this->unconditional = $unconditional;
        }

        /**
         * Provide values and columns that should be updated with the query.
         *
         * Parameter $values is array of key => value pairs where key is name
         * of the affected column and value may be either:
         *   - null (threated as SQL NULL value)
         *   - SQLExpression object
         *   - basic type (string, number, ...)
         *
         * Values are automatically escaped by database rules.
         *
         * @param values Array of column => values pairs.
         * @return self
         *
         * @example
         *   $update->set([
         *     'name' => 'Sherlock',
         *     'surname' => 'Holmes',
         *     'address' => null
         *   ]);
         */
        public function set(array $values) : self
        {
            foreach ($values as $column => $value) {
                if ($value instanceof SQLExpression) {
                    $this->values .= sprintf(
                        ', %s = %s',
                        $this->flavor->column($column),
                        $value->get()
                    );
                    continue;
                }

                $this->values .= sprintf(
                    ', %s = %s',
                    $this->flavor->column($column),
                    $this->flavor->value($value)
                );
            }

            return $this;
        }

        /**
         * Provide condition that must be met in order to update columns.
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
         * Return constructed UPDATE query.
         */
        public function get() : string
        {
            if (empty($this->values)) {
                throw new LogicException("No values to update were set.");
            }

            $query = sprintf(
                'UPDATE %s SET %s',
                $this->flavor->table($this->table),
                ltrim($this->values, ', ')
            );

            if (!empty($this->where)) {
                $query .= ' WHERE ' . $this->where;
            } else if (!$this->unconditional) {
                throw new LogicException("Where clause must be set explicitly.");
            }

            return $query;
        }
    }
?>
