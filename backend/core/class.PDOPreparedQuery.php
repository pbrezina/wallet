<?php declare(strict_types=1);

    namespace Shockie\Core;

    use PDOException;
    use PDOStatement;
    use Shockie\Core\SQLPreparedQuery;
    use Shockie\Core\SQLResult;
    use Shockie\Exceptions\SQLQueryException;

    /**
     * SQL Result Base class.
     *
     * Extend this class to create SQL result accessor.
     */
    class PDOPreparedQuery extends SQLPreparedQuery
    {
        private $statement;
        private $query;

        /**
         * Prepare PDO query for multiple executions.
         *
         * @param PDOStatement $statement PDO statement.
         * @param string $query Query to prepare.
         */
        public function __construct(PDOStatement $statement, string $query)
        {
            $this->statement = $statement;
            $this->query = $query;
        }

        /**
         * @see SQLPreparedQuery::run()
         */
        public function run(array $params = []) : SQLResult
        {
            try {
                $this->statement->execute($params);
                return new PDOResult($this->statement);
            } catch (PDOException $e) {
                throw new SQLQueryException(
                    $this->query,
                    $e->getMessage(),
                    (int)$e->getCode(),
                    $e
                );
            }
        }
    }
?>
