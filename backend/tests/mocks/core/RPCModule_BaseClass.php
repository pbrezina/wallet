<?php declare(strict_types=1);

    namespace Shockie\Tests\Mocks\Core;

    use Exception;
    use Shockie\Core\RPCMethod;
    use Shockie\Core\RPCModule;
    use Shockie\Core\JSONSchema;
    use Shockie\Interfaces\IRPCRequestData;

    final class RPCModule_BaseClass extends RPCModule
    {
        public function __construct()
        {
            $methods = [
                new RPCMethod(
                    'test_method', 1,
                    $this->rpc('rpc_test_method'),
                    null
                ),
                new RPCMethod(
                    'test_method', 2,
                    $this->rpc('rpc_test_method_2'),
                    null
                ),
                new RPCMethod(
                    'test_exception', 1,
                    $this->rpc('rpc_test_exception'),
                    null
                ),
                new RPCMethod(
                    'test_validator', 1,
                    $this->rpc('rpc_test_method'),
                    new JSONSchema('{
                        "type": "object",
                        "properties": {
                            "test": {"type": "boolean"}
                        }
                    }', JSONSchema::STRING)
                )
            ];

            parent::__construct($methods);
        }

        protected function rpc_test_method(IRPCRequestData $rpcdata, object $data)
        {
            return 'this is a test';
        }

        protected function rpc_test_method_2(IRPCRequestData $rpcdata, object $data)
        {
            return 'this is a test 2';
        }

        protected function rpc_test_exception(IRPCRequestData $rpcdata, object $data)
        {
            throw new Exception("test exception");
        }
    }
?>
