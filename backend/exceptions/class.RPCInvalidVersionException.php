<?php declare(strict_types=1);

    namespace Shockie\Exceptions;

    use Exception;
    use Throwable;

    class RPCInvalidVersionException extends Exception
    {
        private $method;
        private $version;

        public function __construct(string $method, int $version, ?Throwable $previous = null)
        {
            $message = sprintf('Invalid version requsted on method "%s" (version %d)',
                               $method, $version);
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
