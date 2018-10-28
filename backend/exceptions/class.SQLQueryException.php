<?php declare(strict_types=1);

    namespace Shockie\Exceptions;

    use Throwable;
    use Shockie\Exceptions\SQLDatabaseException;

    class SQLQueryException extends SQLDatabaseException
    {
        private $query;

        public function __construct(string $query,
                                    string $message,
                                    int $code = 0,
                                    ?Throwable $previous = null)
        {
            parent::__construct($message, $code, $previous);

            $this->query = $query;
        }

        public function getQuery() : string
        {
            return $this->query;
        }
    }
?>
