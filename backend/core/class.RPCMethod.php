<?php declare(strict_types=1);

    namespace Shockie\Core;

    use Shockie\Interfaces\IRPCMethod;
    use Shockie\Interfaces\IRPCRequestData;
    use Shockie\Interfaces\IValidator;

    /**
     * RPC Method.
     *
     * Represents a callable RPC method.
     */
    class RPCMethod implements IRPCMethod
    {
        private $name;
        private $version;
        private $handler;
        private $validator;

        /**
         * Construct an RPC method.
         *
         * Method handler is in the form of:
         *   function handler(IRPCRequestData $request, mixed $data)
         *   where:
         *     $request Is an interface for accessing protocol information
         *              for advanced uses when needed.
         *     $data    Data is method specific input data.
         *
         * @param string $name RPC method name.
         * @param integer $version RPC method version.
         * @param callable $handler Callable method handler.
         * @param IValidator|null $validator Input data validator.
         */
        public function __construct(string $name,
                                    int $version,
                                    callable $handler,
                                    ?IValidator $validator)
        {
            $this->name = $name;
            $this->version = $version;
            $this->handler = $handler;
            $this->validator = $validator;
        }

        /**
         * Get method name.
         *
         * @return string
         */
        public function getName() : string
        {
            return $this->name;
        }

        /**
         * Get method version.
         *
         * @return integer
         */
        public function getVersion() : int
        {
            return $this->version;
        }

        /**
         * Call RPC method.
         *
         * @param IRPCRequestData $rpcdata Input RPC data.
         * @return void
         */
        public function call(IRPCRequestData $rpcdata)
        {
            if ($this->validator !== null) {
                $this->validator->validate($rpcdata->getData());
            }

            return call_user_func($this->handler, $rpcdata, $rpcdata->getData());
        }
    }
?>
