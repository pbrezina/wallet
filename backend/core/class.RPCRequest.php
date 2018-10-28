<?php declare(strict_types=1);

    namespace Shockie\Core;

    use Shockie\Core\JSONSchema;
    use Shockie\Exceptions\InvalidPropertyValueException;
    use Shockie\Interfaces\IRPCProtocol;
    use Shockie\Interfaces\IRPCRequest;

    /**
     * RPC Request.
     *
     * This class holds information about an incomming RPC request.
     */
    class RPCRequest extends RPCProtocol implements IRPCRequest
    {
        private $data;

        /**
         * Construct RPC request from incomming message.
         *
         * The incomming request is validated against schema [1]. If the
         * validation fails then InvalidFormatException is thrown.
         *
         * [1] /schemas/schema.rpc.request.json
         *
         * @param object $rpcdata Incomming message.
         *
         * @throws InvalidFormatException
         */
        public function __construct(object $rpcdata)
        {
            $this->validate($rpcdata);

            parent::__construct($rpcdata->protocol,
                                IRPCProtocol::REQUEST,
                                $rpcdata->module,
                                $rpcdata->method,
                                $rpcdata->version);

            $this->data = $rpcdata->data;
        }

        /**
         * Get RPC request specific data.
         *
         * @return mixed
         */
        public function getData()
        {
            return $this->data;
        }

        private function validate($rpcdata) : void
        {
            /* Validate input against schema. */
            $schema = new JSONSchema(__DIR__ . '/../schemas/schema.rpc.request.json');
            $schema->validate($rpcdata);

            /* Perform request specific validation. */
            if ($rpcdata->type != IRPCProtocol::REQUEST) {
                throw InvalidPropertyValueException::MatchTo(
                    '/type', $rpcdata->type, IRPCProtocol::REQUEST
                );
            }
        }
    }
?>
