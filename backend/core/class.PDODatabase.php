<?php declare(strict_types=1);

    namespace Shockie\Core;

    use PDO;
    use PDOException;
    use Shockie\Core\PDOResult;
    use Shockie\Core\SQLDatabase;
    use Shockie\Core\SQLResult;
    use Shockie\Exceptions\SQLDatabaseException;
    use Shockie\Exceptions\SQLQueryException;
    use Shockie\Exceptions\SQLTransactionException;

    /**
     * Base class for SQL databases.
     *
     * Provides abstraction around arbitrary SQL database.
     */
    abstract class PDODatabase extends SQLDatabase
    {
        private $dsn;
        private $pdo;
        private $transactions;

        public function __construct(string $dsn,
                                    ?string $username = null,
                                    ?string $password = null,
                                    array $options = [])
        {
            parent::__construct();

            $this->dsn = $dsn;
            $this->transactions = 0;

            try {
                $this->pdo = new PDO($dsn, $username, $password, $options);
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                throw new SQLDatabaseException(
                    'Unable to connect to database: ' .$e->getMessage(),
                    (int)$e->getCode(),
                    $e
                );
            }
        }

        /**
         * @see SQLDatabase::lastId()
         */
        public function lastId($name = null) : int
        {
            return (int)$this->pdo->lastInsertId($name);
        }

        /**
         * @see SQLDatabase::startTransaction()
         */
        public function startTransaction() : void
        {
            try {
                if ($this->transactions == 0) {
                    $this->transactions++;
                    return;
                }

                $result = $this->pdo->beginTransaction();
                if ($result === false) {
                    throw new SQLTransactionException(
                        'Unable to start transaction!'
                    );
                }
                $this->transactions++;
            } catch (PDOException $e) {
                throw new SQLTransactionException(
                    'Unable to start transaction: ' .$e->getMessage(),
                    (int)$e->getCode(),
                    $e
                );
            }
        }

        /**
         * @see SQLDatabase::commit()
         */
        public function commit() : void
        {
            if ($this->transactions <= 0) {
                throw new SQLTransactionException(
                    'There are no opened transactions!'
                );
            }

            try {
                /* Only commit if we are on the top of the nesting level. */
                if ($this->transaction == 1) {
                    $result = $this->pdo->commit();
                    if ($result === false) {
                        throw new SQLTransactionException(
                            'Unable to commit transaction!'
                        );
                    }
                }
                $this->transactions--;
            } catch (PDOException $e) {
                throw new SQLTransactionException(
                    'Unable to commit transaction: ' .$e->getMessage(),
                    (int)$e->getCode(),
                    $e
                );
            }
        }

        /**
         * @see SQLDatabase::rollback()
         */
        public function rollback() : void
        {
            if ($this->transactions <= 0) {
                throw new SQLTransactionException(
                    'There are no opened transactions!'
                );
            }

            try {
                /* Only rollback if we are on the top of the nesting level. */
                if ($this->transaction == 1) {
                    $result = $this->pdo->rollBack();
                    if ($result === false) {
                        throw new SQLTransactionException(
                            'Unable to rollback transaction!'
                        );
                    }
                }
                $this->transactions--;
            } catch (PDOException $e) {
                throw new SQLTransactionException(
                    'Unable to rollback transaction: ' .$e->getMessage(),
                    (int)$e->getCode(),
                    $e
                );
            }
        }

        /**
         * Convert PHP typed value into a string to be used in SQL query.
         *
         * @example null -> NULL
         * @example string -> 'string'
         * @example other -> unchanged
         *
         * @return string
         */
        public function value($value) : string
        {
            if ($value === null) {
                return 'NULL';
            }

            if (is_string($value)) {
                return $this->pdo->quote($value);
            }

            return (string)$value;
        }

        /**
         * @see SQLDatabase::prepare()
         */
        public function prepare(string $query) : SQLPreparedQuery
        {
            $prepared = $this->prepareQueryColumns($query);
            $statement = $this->pdo->prepare($prepared);

            return new PDOPreparedQuery($statement, $query);
        }

        /**
         * @see SQLDatabase::executeQuery()
         */
        protected function executeQuery(string $raw,
                                        string $prepared) : SQLResult
        {
            try {
                $result = $this->pdo->query($prepared);
                return new PDOResult($result);
            } catch (PDOException $e) {
                throw new SQLQueryException(
                    $raw,
                    $e->getMessage(),
                    (int)$e->getCode(),
                    $e
                );
            }
        }
    }
?>
