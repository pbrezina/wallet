<?php declare(strict_types=1);

    namespace Shockie\Core;

    use Shockie\Core\RPCProtocol;
    use Shockie\Interfaces\IRPCProtocol;
    use Shockie\Interfaces\IRPCResponse;
    use Shockie\Interfaces\IRPCRequest;

    /**
     * RPC Response.
     *
     * This class holds information about a response to a successful
     * RPC request and construct a reply message in RPC protocol.
     */
    class RPCResponse extends RPCProtocol implements IRPCResponse
    {
        private $data;

        /**
         * Create an RPC response.
         *
         * @param IRPCRequest $reply_to RPC request to reply to.
         * @param mixed $data Response data.
         */
        public function __construct(IRPCRequest $reply_to, $data)
        {
            parent::__construct($reply_to->getProtocolVersion(),
                                IRPCProtocol::RESPONSE,
                                $reply_to->getModuleName(),
                                $reply_to->getMethodName(),
                                $reply_to->getMethodVersion());

            $this->data = $data;
        }

        /**
         * Construct an RPC response in RPC protocol.
         *
         * @return object
         */
        public function getResponse() : object
        {
            $protocol = $this->getAsObject();
            $protocol->data = $this->data;

            return $protocol;
        }
    }
?>
