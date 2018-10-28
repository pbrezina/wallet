<?php declare(strict_types=1);

    namespace Shockie\Modules\RPC;

    use Shockie\Core\JSONSchema;
    use Shockie\Core\RPCMethod;
    use Shockie\Core\RPCModule;
    use Shockie\Interfaces\IRPCRequestData;

    class TestModule_RPC extends RPCModule
    {
        public function __construct()
        {
            $methods = [
                new RPCMethod(
                    'test', 1,
                    $this->rpc('rpc_test'),
                    new JSONSchema('./schemas/schema.rpc.module.test.json')
                )
            ];

            parent::__construct($methods);
        }

        protected function rpc_test(IRPCRequestData $rpcdata, object $data) : object
        {
            $data->test = "tohle je test";
            return $data;
        }
    }
?>
