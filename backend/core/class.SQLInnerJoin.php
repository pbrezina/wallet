<?php declare(strict_types=1);

    namespace Shockie\Core;

    use Shockie\Core\SQLJoin;
    use Shockie\Interfaces\ISQLFlavor;

    /**
     * SQL Inner Join operator.
     */
    class SQLInnerJoin extends SQLJoin
    {
        /**
         * Create '$left INNER JOIN $right' SQL join.
         *
         * @param flavor Provides database-specific functionality.
         * @param left Left-side of the join.
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
        public function __construct(ISQLFlavor $flavor, $left, $right)
        {
            parent::__construct($flavor, $left, 'INNER JOIN', $right);
        }
    }
?>
