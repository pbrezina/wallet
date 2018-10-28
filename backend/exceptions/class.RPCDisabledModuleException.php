<?php declare(strict_types=1);

    namespace Shockie\Exceptions;

    use Exception;
    use Throwable;

    class RPCDisabledModuleException extends Exception
    {
        private $module;

        public function __construct(string $module, ?Throwable $previous = null)
        {
            $message = sprintf('Module "%s" is disabled', $module);
            parent::__construct($message, 0, $previous);

            $this->module = $module;
        }

        public function getModuleName() : string
        {
            return $this->module;
        }
    }
?>
