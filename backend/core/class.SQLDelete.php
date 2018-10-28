<?php declare(strict_types=1);

    namespace Shockie\Core;

    use InvalidArgumentException;
    use LogicException;
    use Shockie\Core\SQLExpression;
    use Shockie\Core\SQLQuery;
    use Shockie\Interfaces\ISQLFlavor;

    /**
     * SQL Delete Query.
     *
     * Construct an SQL DELETE query formatted as:
     * DELETE FROM table WHERE expression
     */
    class SQLDelete extends SQLQuery
    {
        private $table;
        private $where;
        private $unconditional;

        /**
         * @param flavor Provides database-specific functionality.
         * @param table Table to delete rows from.
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
            $this->where = '';
            $this->unconditional = $unconditional;
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
         * Return constructed DELETE query.
         */
        public function get() : string
        {
            $query  = 'DELETE FROM ' . $this->flavor->table($this->table);

            if (!empty($this->where)) {
                $query .= ' WHERE ' . $this->where;
            } else if (!$this->unconditional) {
                throw new LogicException("Where clause must be set explicitly.");
            }

            return $query;
        }
    }
?>
