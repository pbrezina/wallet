<?php declare(strict_types=1);
    namespace Shockie\Tests\Core;

    use PHPUnit\Framework\TestCase;
    use Shockie\Core\RPCModule;
    use Shockie\Core\RPCRequest;
    use Shockie\Core\RPCRequestData;
    use Shockie\Core\JSON;
    use Shockie\Exceptions\RPCUnknownMethodException;
    use Shockie\Exceptions\RPCInvalidVersionException;
    use Shockie\Exceptions\RPCCallException;
    use Shockie\Tests\Mocks\Core\RPCModule_BaseClass;

    final class RPCModuleTest extends TestCase
    {
        private $module;

        protected function setUp()
        {
            $this->module = new RPCModule_BaseClass();
        }

        protected function tearDown()
        {
            unset($this->module);
        }

        public function testCall_valid() : void
        {
            $json = '{
                "protocol" : 1,
                "type" : "request",
                "module" : "test_module",
                "method" : "test_method",
                "version" : 1,
                "data" : {"test" : true}
            }';

            $request = new RPCRequest(JSON::Parse($json));
            $data = new RPCRequestData($request);

            $result = $this->module->call($data);
            $this->assertEquals(
                $result,
                'this is a test'
            );
        }

        public function testCall_valid_different_version() : void
        {
            $json = '{
                "protocol" : 1,
                "type" : "request",
                "module" : "test_module",
                "method" : "test_method",
                "version" : 2,
                "data" : {"test" : true}
            }';

            $request = new RPCRequest(JSON::Parse($json));
            $data = new RPCRequestData($request);

            $result = $this->module->call($data);
            $this->assertEquals(
                $result,
                'this is a test 2'
            );
        }

        public function testCall_unknown_method() : void
        {
            $json = '{
                "protocol" : 1,
                "type" : "request",
                "module" : "test_module",
                "method" : "unknown_method",
                "version" : 1,
                "data" : {"test" : true}
            }';

            $request = new RPCRequest(JSON::Parse($json));
            $data = new RPCRequestData($request);

            $this->expectException(RPCUnknownMethodException::class);
            $this->module->call($data);
        }

        public function testCall_unknown_version() : void
        {
            $json = '{
                "protocol" : 1,
                "type" : "request",
                "module" : "test_module",
                "method" : "test_method",
                "version" : 10,
                "data" : {"test" : true}
            }';

            $request = new RPCRequest(JSON::Parse($json));
            $data = new RPCRequestData($request);

            $this->expectException(RPCInvalidVersionException::class);
            $this->module->call($data);
        }

        public function testCall_exception_thrown() : void
        {
            $json = '{
                "protocol" : 1,
                "type" : "request",
                "module" : "test_module",
                "method" : "test_exception",
                "version" : 1,
                "data" : {"test" : true}
            }';

            $request = new RPCRequest(JSON::Parse($json));
            $data = new RPCRequestData($request);

            $this->expectException(RPCCallException::class);
            $this->module->call($data);
        }

        public function testCall_validator_valid() : void
        {
            $json = '{
                "protocol" : 1,
                "type" : "request",
                "module" : "test_module",
                "method" : "test_validator",
                "version" : 1,
                "data" : {"test" : true}
            }';

            $request = new RPCRequest(JSON::Parse($json));
            $data = new RPCRequestData($request);

            $result = $this->module->call($data);
            $this->assertEquals(
                $result,
                'this is a test'
            );
        }

        public function testCall_validator_invalid() : void
        {
            $json = '{
                "protocol" : 1,
                "type" : "request",
                "module" : "test_module",
                "method" : "test_validator",
                "version" : 1,
                "data" : {"test" : "true"}
            }';

            $request = new RPCRequest(JSON::Parse($json));
            $data = new RPCRequestData($request);

            $this->expectException(RPCCallException::class);
            $this->module->call($data);
        }
    }
?>
