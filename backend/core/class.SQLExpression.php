<?php declare(strict_types=1);

    namespace Shockie\Core;

    use InvalidArgumentException;
    use LogicException;
    use Shockie\Interfaces\ISQLFlavor;

    class SQLExpression
    {
        private $flavor;
        private $expression;
        private $num_brackets;

        /**
         * @param ISQLFlavor $flavor Provides database-specific functionality.
         */
        public function __construct(ISQLFlavor $flavor)
        {
            $this->flavor = $flavor;
            $this->expression = '';
            $this->num_brackets = 0;
        }

        /**
         * Return constructed expression.
         *
         * @throws LogicExpression If number of closing and opening brackets do not match.
         *
         * @return string
         */
        public function get() : string
        {
            if ($this->num_brackets != 0) {
                throw new LogicException('Closing brackets do not match opening brackets.');
            }

            return trim($this->expression);
        }

        /**
         * Insert a custom SQL expression.
         *
         * @param string $expression SQL expression.
         * @param array §params Array of parameter values in 'key' => 'value'
         *              pairs that will be replaced in the expression.
         * @return self
         *
         * $expression is an arbitrary SQL expression which in addition
         * can contain several parametrized arguments.
         *
         * @see SQLDatabase::prepareQueryString() for the format.
         *
         * @throws LogicExpression If an parameter is present in the expression but is not set.
         *
         * @example Filter rows by id.
         *   custom('::id = :id', ['id' => 5]);
         *
         * @example Find all users which are named John.
         *   custom('::name LIKE :name', ['name' => 'John%']);
         *
         */
        public function custom(string $expression, array $params = []) : self
        {
            if (empty($params)) {
                $this->expression .= $this->flavor->prepareQueryColumns(
                    $expression
                );

                return $this;
            }

            $this->expression .= $this->flavor->prepareQueryString(
                $expression, $params
            );

            return $this;
        }

        /**
         * Add opening bracket.
         *
         * @return self
         */
        public function open() : self
        {
            $this->expression .= '(';
            $this->num_brackets++;

            return $this;
        }

        /**
         * Add closing bracket.
         *
         * @return self
         */
        public function close() : self
        {
            if ($this->num_brackets <= 0) {
                throw new LogicException("Missing opening bracket");
            }

            $this->expression .= ')';
            $this->num_brackets--;

            return $this;
        }

        /**
         * Add NOT operator.
         *
         * @return self
         */
        public function not() : self
        {
            $this->expression .= ' NOT ';

            return $this;
        }

        /**
         * Add AND operator.
         *
         * @return self
         */
        public function and() : self
        {
            $this->expression .= ' AND ';

            return $this;
        }

        /**
         * Add OR operator.
         *
         * @return self
         */
        public function or() : self
        {
            $this->expression .= ' OR ';

            return $this;
        }

        /**
         * Compare $column with $value using operator $operator.
         *
         * Both column and value are escaped per database rules.
         *
         * @param string $column
         * @param string $operator
         * @param mixed $value
         *
         * @example Filter rows by id.
         *   value('id', '=', 5);
         *
         * @return self
         */
        public function value(string $column, string $operator, $value) : self
        {
            $this->expression .= sprintf(
                '%s %s %s',
                $this->flavor->column($column),
                $operator,
                $this->flavor->value($value)
            );

            return $this;
        }

        /**
         * Compare $column with another column using operator $operator.
         *
         * Both columns are escaped per database rules.
         *
         * @param string $column_left
         * @param string $operator
         * @param string $column_right
         *
         * @example Filter rows by id.
         *   column('table1.id', '=', 'table2.id');
         *
         * @return self
         */
        public function column(string $column_left, string $operator, string $column_right) : self
        {
            $this->expression .= sprintf(
                '%s %s %s',
                $this->flavor->column($column_left),
                $operator,
                $this->flavor->column($column_right)
            );

            return $this;
        }

        /**
         * Compare $column using BETWEEN operator.
         *
         * Columns and values are escaped per database rules.
         *
         * @param string $column
         * @param mixed $left
         * @param mixed $right
         *
         * @example Find rows which id is between 5 and 10.
         *   between('id', 5, 10);
         *
         * @return self
         */
        public function between(string $column, $left, $right) : self
        {
            $this->expression .= sprintf(
                '%s BETWEEN %s AND %s',
                $this->flavor->column($column),
                $this->flavor->value($left),
                $this->flavor->value($right)
            );

            return $this;
        }

        /**
         * Compare $column using IN operator.
         *
         * Columns and values are escaped per database rules.
         *
         * @param string $column
         * @param array $values
         *
         * @example Find rows which id is in specified list.
         *   in('id', [5, 6, 7, 8]);
         *
         * @return self
         */
        public function in(string $column, array $values) : self
        {
            $list = '';
            foreach ($values as $value) {
                $list .= $this->flavor->value($value) . ', ';
            }

            if (empty($list)) {
                throw new InvalidArgumentException("No values were specified.");
            }

            $this->expression .= sprintf(
                '%s IN (%s)',
                $this->flavor->column($column),
                rtrim($list, ', ')
            );

            return $this;
        }

        /**
         * Compare $column using LIKE operator.
         *
         * Both column and value are escaped per database rules.
         *
         * @param string $column
         * @param mixed $value
         *
         * @example Find all users which are named John.
         *   like('name', 'John%');
         *
         * @return self
         */
        public function like(string $column, $value) : self
        {
            $this->expression .= sprintf(
                '%s LIKE %s',
                $this->flavor->column($column),
                $this->flavor->value($value)
            );

            return $this;
        }

        /**
         * Compare $column using IS NULL operator.
         *
         * Column is escaped per database rules.
         *
         * @param string $column
         *
         * @example Find all users without an e-mail specified.
         *   isNull('email');
         *
         * @return self
         */
        public function isNull(string $column) : self
        {
            $this->expression .= sprintf(
                '%s IS NULL',
                $this->flavor->column($column)
            );

            return $this;
        }

        /**
         * Compare $column using IS NOT NULL operator.
         *
         * Column is escaped per database rules.
         *
         * @param string $column
         *
         * @example Find all users with an e-mail specified.
         *   isNotNull('email');
         *
         * @return self
         */
        public function isNotNull(string $column) : self
        {
            $this->expression .= sprintf(
                '%s IS NOT NULL',
                $this->flavor->column($column)
            );

            return $this;
        }

        /**
         * Construct a condition in various ways and return it.
         *
         * @param ISQLFlavor $flavor Provides database-specific functionality.
         * @param ...$args Variable number of arguments.
         * @return self
         *
         * This is a helper method to simplify creating conditions for SQL queries
         * and it should not be called manually. Format of arguments for queries
         * methods is described bellow.
         *
         * - ()
         *   - equivalent of ''
         *
         * - (SQLExpression $expression)
         *   - equivalent of $expression->get();
         *
         * - (string $expression)
         *   - equivalent of (new SQLExpression(ISQLFlavor))->custom($expression)->get();
         *
         * - (string $expression, array $parameters)
         *   - equivalent of (new SQLExpression(ISQLFlavor))->custom($expression, $parameters)->get();
         *
         * - (string $column, string $operator, $value)
         *   - equivalent of (new SQLExpression(ISQLFlavor))->value($column, $operator, $value)->get();
         *
         */
        static public function Condition(ISQLFlavor $flavor, ...$args) : string
        {
            switch (count($args)) {
            case 0:
                return '';
            case 1:
                if ($args[0] instanceof SQLExpression) {
                    $expression = $args[0];
                    break;
                }

                $expression = new SQLExpression($flavor);
                $expression->custom($args[0]);
                break;
            case 2:
                $expression = new SQLExpression($flavor);
                $expression->custom($args[0], $args[1]);
                break;
            case 3:
                $expression = new SQLExpression($flavor);
                $expression->value($args[0], $args[1], $args[2]);
                break;
            default:
                throw new LogicException('Invalid number of arguments.');
            }

            return $expression->get();
        }
    }
?>
