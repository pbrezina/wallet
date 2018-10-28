<?php declare(strict_types=1);

    namespace Shockie\Core;

    use Throwable;
    use Shockie\Core\RPCProtocol;
    use Shockie\Interfaces\IRPCProtocol;
    use Shockie\Interfaces\IRPCRequest;
    use Shockie\Interfaces\IRPCResponse;

    /**
     * RPC Error.
     *
     * This class holds information about an error in an RPC call and
     * constructs error message in RPC protocol.
     */
    class RPCError extends RPCProtocol implements IRPCResponse
    {
        private $error;

        /**
         * Create an RPC error.
         *
         * @param IRPCRequest|null $reply_to Initial RPC request that caused the error.
         * @param Throwable $error The error.
         */
        public function __construct(?IRPCRequest $reply_to, Throwable $error)
        {
            if ($reply_to === null) {
                /* Error happened before we were able to parse protocol data. */
                parent::__construct(1, IRPCProtocol::ERROR, '(unknown)', '(unknown)', 1);
            } else {
                parent::__construct($reply_to->getProtocolVersion(),
                                    IRPCProtocol::ERROR,
                                    $reply_to->getModuleName(),
                                    $reply_to->getMethodName(),
                                    $reply_to->getMethodVersion());
            }

            $this->error = $error;
        }

        /**
         * Construct an RPC error response in RPC protocol.
         *
         * @return object
         */
        public function getResponse() : object
        {
            $protocol = $this->getAsObject();
            $protocol->data = (object)[
                'errors' => $this->buildErrors($this->error),
                'backtrace' => $this->buildBacktrace($this->error)
            ];

            return $protocol;
        }

        private function buildErrors(Throwable $first) : array
        {
            $errors = [];
            $current = $first;
            do {
                $errors[] = $this->throwableToResponseData($current);
            } while($current = $current->getPrevious());

            return array_reverse($errors);
        }

        private function buildBacktrace($error) : array
        {
            $stack = [];

            /* Find the first exception. */
            while ($error->getPrevious()) {
                $error = $error->getPrevious();
            }

            $backtrace = $error->getTrace();
            $count = count($backtrace);
            for($i = 0; $i < $count; $i++) {
                $next = $i < $count - 1 ? $backtrace[$i + 1] : null;
                $stack[] = $this->traceToResponseData($backtrace[$i], $next);
            }

            array_unshift($stack, [
                'call' => 'new ' . get_class($error) . '()',
                'from' => $stack[0]->call ?? '__main__',
                'file' => $error->getFile(),
                'line' => $error->getLine(),
            ]);

            return $stack;
        }

        private function traceToResponseData(array $trace, ?array $next) : object
        {
            $data = [
                'call' => $this->traceToFunctionName($trace),
                'from' => $this->traceToFunctionName($next),
                'file' => $trace['file'] ?? null,
                'line' => $trace['line'] ?? null
            ];

            return (object)$data;
        }

        private function traceToFunctionName(?array $trace)
        {
            if ($trace === null) {
                return '__main__';
            }

            return sprintf("%s%s%s",
                           $trace['class'] ?? '',
                           $trace['type'] ?? '',
                           $trace['function'] ?? '');
        }

        private function throwableToResponseData($error) : object
        {
            $data = [
                'class' => get_class($error),
                'message' => $error->getMessage(),
                'code' => $error->getCode(),
                'file' => $error->getFile(),
                'line' => $error->getLine()
            ];

            return (object)$data;
        }
    }
?>
