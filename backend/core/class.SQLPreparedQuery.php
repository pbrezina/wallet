<?php declare(strict_types=1);

    namespace Shockie\Core;

    use Shockie\Core\SQLDatabase;
    use Shockie\Core\SQLResult;

    /**
     * SQL Result Base class.
     *
     * Extend this class to create SQL result accessor.
     */
    class SQLPreparedQuery
    {
        private $db;
        private $query;

        /**
         * Prepare SQL query for multiple executions.
         *
         * @param SQLDatabase $db SQL Database.
         * @param string $query Query to prepare.
         */
        public function __construct(SQLDatabase $db, string $query)
        {
            $this->query = $query;
            $this->db = $db;
        }

        /**
         * Execute SQL query with possible parameters.
         *
         * @param array $params Array of parameter values in 'key' => 'value'
         *              pairs that will be replaced in the query.
         *
         * @throws Shockie\Exceptions\SQLQueryException;
         *
         * @return SQLResult
         */
        public function run(array $params = []) : SQLResult
        {
            $query = $this->db->prepareQueryString($this->query, $params);

            return $this->db->run($query);
        }
    }
?>
