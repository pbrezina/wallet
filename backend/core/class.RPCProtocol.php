<?php declare(strict_types=1);

    namespace Shockie\Core;

    use Shockie\Interfaces\IRPCProtocol;

    /**
     * RPC Protocol base class.
     *
     * This is a base class for implementation of specific RPC messages.
     *
     * @see RPCRequest
     * @see RPCResponse
     * @see RPCError
     */
    abstract class RPCProtocol implements IRPCProtocol
    {
        private $protocol_version;
        private $type;
        private $module;
        private $method;
        private $method_version;
        private $data;

        /**
         * Create an RPC protocol object.
         *
         * @param integer $protocol_version RPC protocol version.
         * @param string $type RPC message type (request | error | response)
         * @param string $module RPC module name.
         * @param string $method RPC method name.
         * @param integer $method_version RPC method version.
         *
         * @see IRPCProtocol::REQUEST
         * @see IRPCProtocol::ERROR
         * @see IRPCProtocol::RESPONSE
         */
        public function __construct(int $protocol_version,
                                    string $type,
                                    string $module,
                                    string $method,
                                    int $method_version)
        {
            $this->protocol_version = $protocol_version;
            $this->type = $type;
            $this->module = $module;
            $this->method = $method;
            $this->method_version = $method_version;
        }

        /**
         * Get RPC protocol version.
         *
         * @return integer
         */
        public function getProtocolVersion() : int
        {
            return $this->protocol_version;
        }

        /**
         * Get RPC message type.
         *
         * @return string
         */
        public function getType() : string
        {
            return $this->type;
        }

        /**
         * Get RPC module name.
         *
         * @return string
         */
        public function getModuleName() : string
        {
            return $this->module;
        }

        /**
         * Get RPC method name.
         *
         * @return string
         */
        public function getMethodName() : string
        {
            return $this->method;
        }

        /**
         * Get RPC method version.
         *
         * @return integer
         */
        public function getMethodVersion() : int
        {
            return $this->method_version;
        }

        /**
         * Return RPC protocol header as object.
         *
         * @return object
         */
        protected function getAsObject() : object
        {
            $fields = array(
                'protocol' => $this->protocol_version,
                'type' => $this->type,
                'module' => $this->module,
                'method' => $this->method,
                'version' => $this->method_version
            );

            return (object)$fields;
        }
    }
?>
