<?php declare(strict_types=1);

    namespace Shockie\Core;

    use Shockie\Core\RPCRequest;
    use Shockie\Interfaces\IRPCRequest;
    use Shockie\Interfaces\IRPCRequestData;

    /**
     * RPC Request Protocol Data.
     *
     * Holds information about the input data of an RPC request.
     */
    class RPCRequestData implements IRPCRequestData
    {
        private $request;

        /**
         * Construct request data.
         *
         * @param RPCRequest $request The RPC request.
         */
        public function __construct(RPCRequest $request)
        {
            $this->request = $request;
        }

        /**
         * Get the whole RPC request object.
         *
         * @return IRPCRequest
         */
        public function getRequest() : IRPCRequest
        {
            return $this->request;
        }

        /**
         * Get RPC method name.
         *
         * @return string
         */
        public function getMethodName() : string
        {
            return $this->request->getMethodName();
        }

        /**
         * Get RPC method version.
         *
         * @return integer
         */
        public function getMethodVersion() : int
        {
            return $this->request->getMethodVersion();
        }

        /**
         * Get RPC method input data.
         *
         * @return mixed
         */
        public function getData()
        {
            return $this->request->getData();
        }
    }
?>
