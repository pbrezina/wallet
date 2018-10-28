<?php declare(strict_types=1);

    namespace Shockie\Exceptions;

    use Exception;
    use Throwable;

    class IOException extends Exception
    {
        public function __construct(string $message,
                                    ?Throwable $previous = null)
        {
            parent::__construct($message, 0, $previous);
        }
    }
?>
