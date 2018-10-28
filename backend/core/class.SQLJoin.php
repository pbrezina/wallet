<?php declare(strict_types=1);

    namespace Shockie\Core;

    use InvalidArgumentException;
    use Shockie\Core\SQLExpression;
    use Shockie\Interfaces\ISQLFlavor;

    /**
     * SQL Base class for JOIN operators.
     *
     * Extend this class to create SQL JOIN operator builder.
     */
    abstract class SQLJoin
    {
        /**
         * @var ISQLFlavor Provides database-specific functionality.
         */
        protected $flavor;

        private $left;
        private $right;
        private $operator;
        private $on;

        /**
         * @param flavor Provides database-specific functionality.
         * @param left Left-side of the join.
         * @param opeartor Join operator.
         * @param right Right-side of the join.
         *
         * $left and $right can be one of:
         *
         * - (string) 'table'
         *   - table name
         *
         * - (array) (string) 'alias' => (string) 'table'
         *   - aliased table name
         *
         * - (array) (string) 'alias' => (SQLSelect) query
         *   - aliased arbitrary SELECT query
         */
        public function __construct(ISQLFlavor $flavor,
                                    $left,
                                    string $operator,
                                    $right)
        {
            $this->flavor = $flavor;
            $this->left = $left;
            $this->operator = $operator;
            $this->right = $right;
        }

        /**
         * Set join condition.
         *
         * @param args See SQLExpression::Condition for format.
         * @return self
         */
        public function on(...$args) : self
        {
            $this->on = SQLExpression::Condition($this->flavor, ...$args);

            return $this;
        }

        /**
         * Return constructed JOIN operator.
         */
        public function get() : string
        {
            $statement = sprintf(
                '%s %s %s',
                $this->getOperand($this->left),
                $this->operator,
                $this->getOperand($this->right)
            );

            if (!empty($this->on)) {
                $statement .= ' ON ' . $this->on;
            }

            return $statement;
        }

        private function getOperand($operand) : string
        {
            if (empty($operand)) {
                throw new InvalidArgumentException(
                    'Join operand must not be empty.'
                );
            }

            if (is_array($operand)) {
                if (count($operand) != 1) {
                    throw new InvalidArgumentException(
                        'More than one element given as join operand.'
                    );
                }

                $alias = $this->flavor->column(key($operand));
                $value = current($operand);

                if ($value instanceof SQLSelect) {
                    return sprintf('(%s) AS %s', $value->get(), $alias);
                }

                return sprintf('%s AS %s', $this->flavor->table($value), $alias);
            }

            return $this->flavor->table($operand);
        }
    }
?>
