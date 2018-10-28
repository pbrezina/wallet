<?php declare(strict_types=1);

    namespace Shockie\Tests\Core;

    use Closure;
    use Throwable;
    use PHPUnit\Framework\TestCase;
    use Shockie\Core\RPCMethod;
    use Shockie\Core\RPCRequest;
    use Shockie\Core\RPCRequestData;
    use Shockie\Core\JSON;
    use Shockie\Core\JSONSchema;
    use Shockie\Exceptions\InvalidPropertyTypeException;
    use Shockie\Interfaces\IRPCRequestData;

    final class RPCMethodTest extends TestCase
    {
        private $method;
        private $current_request;
        private $cb_called;
        private $null_validator;

        protected function setUp()
        {
            $schema = new JSONSchema('{
                "type": "object",
                "properties": {
                    "test": {"type": "boolean"}
                }
            }', JSONSchema::STRING);

            $this->method = new RPCMethod('test_method', 1,
                Closure::fromCallable([$this, 'callback_test']),
                $schema
            );

            $this->current_request = null;
            $this->cb_called = false;
            $this->null_validator = false;
        }

        protected function tearDown()
        {
            unset($this->data);
        }

        private function callback_test(IRPCRequestData $rpcdata,
                                       object $data) : object
        {
            $this->assertEquals('test_method', $rpcdata->getMethodName());
            $this->assertEquals(1, $rpcdata->getMethodVersion());
            $this->assertSame($this->current_request, $rpcdata->getRequest());

            if (!$this->null_validator) {
                $this->assertEquals(
                    (object)["test" => true],
                    $rpcdata->getData()
                );

                $this->assertEquals(
                    (object)["test" => true],
                    $data
                );
            }

            $this->cb_called = true;

            return (object)["success" => true];
        }

        public function testGetName() : void
        {
            $this->assertEquals(
                'test_method',
                $this->method->getName()
            );
        }

        public function testGetVersion() : void
        {
            $this->assertEquals(
                1,
                $this->method->getVersion()
            );
        }

        public function testCall_valid_data() : void
        {
            $this->current_request = new RPCRequest(JSON::Parse('{
                "protocol" : 1,
                "type" : "request",
                "module" : "test_module",
                "method" : "test_method",
                "version" : 1,
                "data" : {"test" : true}
            }'));

            $data = new RPCRequestData($this->current_request);
            $response = $this->method->call($data);

            $this->assertTrue($this->cb_called);
            $this->assertEquals(
                (object)["success" => true],
                $response
            );
        }

        public function testCall_invalid_data() : void
        {
            $this->current_request = new RPCRequest(JSON::Parse('{
                "protocol" : 1,
                "type" : "request",
                "module" : "test_module",
                "method" : "test_method",
                "version" : 1,
                "data" : {"test" : "true"}
            }'));

            $data = new RPCRequestData($this->current_request);

            $this->expectException(InvalidPropertyTypeException::class);
            try {
                $response = $this->method->call($data);
            } catch (Throwable $e) {
                $this->assertFalse($this->cb_called);
                throw $e;
            }
        }

        public function testCall_null_validator() : void
        {
            $method = new RPCMethod('test_method', 1,
                Closure::fromCallable([$this, 'callback_test']),
                null
            );

            $this->current_request = new RPCRequest(JSON::Parse('{
                "protocol" : 1,
                "type" : "request",
                "module" : "test_module",
                "method" : "test_method",
                "version" : 1,
                "data" : {"test" : "true"}
            }'));

            $this->null_validator = true;
            $data = new RPCRequestData($this->current_request);
            $response = $method->call($data);

            $this->assertTrue($this->cb_called);
            $this->assertEquals(
                (object)["success" => true],
                $response
            );
        }
    }
?>
