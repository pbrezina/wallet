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
    class SQLInsert extends SQLQuery
    {
        private $table;
        private $columns;
        private $values;

        /**
         * @param flavor Provides database-specific functionality.
         * @param table Table to which new row will be inserted.
         */
        public function __construct(ISQLFlavor $flavor,
                                    string $table)
        {
            parent::__construct($flavor);

            if (empty($table)) {
                throw new InvalidArgumentException('Table name must not be empty.');
            }

            $this->table = $table;
            $this->columns = '';
            $this->values = '';
        }

        /**
         * Provide values and columns that should be inserted into table.
         *
         * Parameter $values is array of key => value pairs where key is name
         * of the affected column and value may be either:
         *   - null (threated as SQL NULL value)
         *   - SQLExpression object
         *   - basic type (string, number, ...)
         *
         * Values are automatically escaped by database rules.
         *
         * @param values Array of column => value pairs.
         * @return self
         *
         * @example
         *   $insert->set([
         *     'name' => 'Sherlock',
         *     'surname' => 'Holmes'
         *     'address' => null
         *   ]);
         */
        public function set(array $values) : self
        {
            foreach ($values as $column => $value) {
                $this->columns .= sprintf(', %s', $this->flavor->column($column));

                if ($value instanceof SQLExpression) {
                    $this->values .= sprintf(', %s', $value->get());
                } else {
                    $this->values .= sprintf(', %s', $this->flavor->value($value));
                }
            }

            return $this;
        }

        /**
         * Return constructed INSERT query.
         */
        public function get() : string
        {
            if (empty($this->columns)) {
                throw new LogicException("No columns specified.");
            }

            if (empty($this->values)) {
                throw new LogicException("No values specified.");
            }

            $query = sprintf(
                'INSERT INTO %s (%s) VALUES (%s)',
                $this->flavor->table($this->table),
                ltrim($this->columns, ', '),
                ltrim($this->values, ', ')
            );

            return $query;
        }
    }
?>
