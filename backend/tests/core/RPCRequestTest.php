<?php declare(strict_types=1);

    namespace Shockie\Tests\Core;

    use PHPUnit\Framework\TestCase;
    use Shockie\Core\RPCRequest;
    use Shockie\Core\JSON;
    use Shockie\Exceptions\InvalidPropertyLengthException;
    use Shockie\Exceptions\InvalidPropertyValueException;
    use Shockie\Interfaces\IRPCProtocol;
    use Shockie\Interfaces\IRPCRequest;

    final class RPCRequestTest extends TestCase
    {
        public function testConstruct_invalid_protocol() : void
        {
            $this->expectException(InvalidPropertyValueException::class);
            $request = new RPCRequest(JSON::Parse('{
                "protocol" : 0,
                "type" : "error",
                "module" : "test_module",
                "method" : "test_method",
                "version" : 1,
                "data" : {"test" : true}
            }'));
        }

        public function testConstruct_invalid_type() : void
        {
            $this->expectException(InvalidPropertyValueException::class);
            $request = new RPCRequest(JSON::Parse('{
                "protocol" : 1,
                "type" : "error",
                "module" : "test_module",
                "method" : "test_method",
                "version" : 1,
                "data" : {"test" : true}
            }'));
        }

        public function testConstruct_invalid_module() : void
        {
            $this->expectException(InvalidPropertyValueException::class);
            $request = new RPCRequest(JSON::Parse('{
                "protocol" : 1,
                "type" : "error",
                "module" : "",
                "method" : "test_method",
                "version" : 1,
                "data" : {"test" : true}
            }'));
        }

        public function testConstruct_invalid_method() : void
        {
            $this->expectException(InvalidPropertyValueException::class);
            $request = new RPCRequest(JSON::Parse('{
                "protocol" : 1,
                "type" : "error",
                "module" : "test_module",
                "method" : "",
                "version" : 1,
                "data" : {"test" : true}
            }'));
        }

        public function testConstruct_invalid_version() : void
        {
            $this->expectException(InvalidPropertyValueException::class);
            $request = new RPCRequest(JSON::Parse('{
                "protocol" : 0,
                "type" : "error",
                "module" : "test_module",
                "method" : "test_method",
                "version" : 0,
                "data" : {"test" : true}
            }'));
        }

        /**
         * @dataProvider requestProvider
         */
        public function testGetData(RPCRequest $request) : void
        {
            $this->assertEquals(
                (object)["test" => true],
                $request->getData()
            );
        }

        /**
         * @dataProvider requestProvider
         */
        public function testGetProtocolVersion(RPCRequest $request) : void
        {
            $this->assertEquals(
                1,
                $request->getProtocolVersion()
            );
        }

        /**
         * @dataProvider requestProvider
         */
        public function testGetType(RPCRequest $request) : void
        {
            $this->assertEquals(
                IRPCProtocol::REQUEST,
                $request->getType()
            );
        }

        /**
         * @dataProvider requestProvider
         */
        public function testGetModuleName(RPCRequest $request) : void
        {
            $this->assertEquals(
                "test_module",
                $request->getModuleName()
            );
        }

        /**
         * @dataProvider requestProvider
         */
        public function testGetMethodName(RPCRequest $request) : void
        {
            $this->assertEquals(
                "test_method",
                $request->getMethodName()
            );
        }

        /**
         * @dataProvider requestProvider
         */
        public function testGetMethodVersion(RPCRequest $request) : void
        {
            $this->assertEquals(
                1,
                $request->getMethodVersion()
            );
        }

        public function requestProvider() : array
        {
            $json = '{
                "protocol" : 1,
                "type" : "request",
                "module" : "test_module",
                "method" : "test_method",
                "version" : 1,
                "data" : {"test" : true}
            }';

            return [[new RPCRequest(JSON::Parse($json))]];
        }
    }
?>
