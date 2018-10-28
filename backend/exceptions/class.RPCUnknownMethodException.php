<?php declare(strict_types=1);

    namespace Shockie\Exceptions;

    use Exception;
    use Throwable;

    class RPCUnknownMethodException extends Exception
    {
        private $method;

        public function __construct(string $method, ?Throwable $previous = null)
        {
            $message = sprintf('Method "%s" is not known', $method);
            parent::__construct($message, 0, $previous);

            $this->method = $method;
        }

        public function getRPCMethod() : string
        {
            return $this->method;
        }
    }
?>
