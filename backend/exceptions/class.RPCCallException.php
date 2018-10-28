<?php declare(strict_types=1);

    namespace Shockie\Exceptions;

    use Exception;
    use Throwable;

    class RPCCallException extends Exception
    {
        private $method;
        private $version;

        public function __construct(string $method, int $version, Throwable $previous)
        {
            $message = sprintf('RPC call "%s" (version %d) issued %s',
                               $method, $version, get_class($previous));
            parent::__construct($message, 0, $previous);
        }

        public function getRPCMethod() : string
        {
            return $this->method;
        }

        public function getRPCMethodVersion() : int
        {
            return $this->version;
        }
    }
?>
