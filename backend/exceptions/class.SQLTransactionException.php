<?php declare(strict_types=1);

    namespace Shockie\Exceptions;

    use Throwable;
    use Shockie\Exceptions\SQLDatabaseException;

    class SQLTransactionException extends SQLDatabaseException
    {
        public function __construct(string $message,
                                    int $code = 0,
                                    ?Throwable $previous = null)
        {
            parent::__construct($message, $code, $previous);
        }
    }
?>
