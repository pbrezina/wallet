<?php declare(strict_types=1);

    namespace Shockie\Core;

    use Shockie\Core\SQLPreparedQuery;
    use Shockie\Core\SQLResult;
    use Shockie\Interfaces\ISQLFlavor;

    /**
     * SQL Query Base class.
     *
     * Extend this class to create SQL query builder.
     */
    abstract class SQLQuery
    {
        /**
         * @var ISQLFlavor Provides database-specific functionality.
         */
        protected $flavor;

        /**
         * @param flavor Provides database-specific functionality.
         */
        public function __construct(ISQLFlavor $flavor)
        {
            $this->flavor = $flavor;
        }

        /**
         * Prepare SQL query for multiple executions.
         *
         * @return SQLPreparedQuery
         */
        public function prepare() : SQLPreparedQuery
        {
            return $this->flavor->prepare($this->get());
        }

        /**
         * Run query on database.
         *
         * @param array §params Array of parameter values in 'key' => 'value'
         *              pairs that will be replaced in the query.
         *
         * @return SQLResult
         */
        public function run(array $params = []) : SQLResult
        {
            return $this->flavor->run($this->get(), $params);
        }

        /**
         * Return constructed query.
         *
         * @return string
         */
        abstract public function get() : string;
    }
?>
