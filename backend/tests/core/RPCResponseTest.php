<?php declare(strict_types=1);

    namespace Shockie\Tests\Core;

    use PHPUnit\Framework\TestCase;
    use Shockie\Core\RPCRequest;
    use Shockie\Core\RPCResponse;
    use Shockie\Core\JSON;
    use Shockie\Interfaces\IRPCProtocol;

    final class RPCResponseTest extends TestCase
    {
        /**
         * @dataProvider requestProvider
         */
        public function testGetResponse(RPCRequest $request) : void
        {
            $response = new RPCResponse($request, (object)["response" => true]);

            $this->assertEquals(
                (object)[
                    "protocol" => 1,
                    "type" => IRPCProtocol::RESPONSE,
                    "module" => "test_module",
                    "method" => "test_method",
                    "version" => 1,
                    "data" => (object)["response" => true]
                ],
                $response->getResponse()
            );
        }

        /**
         * @dataProvider requestProvider
         */
        public function testGetResponse_emptyData(RPCRequest $request) : void
        {
            $response = new RPCResponse($request, (object)[]);

            $this->assertEquals(
                (object)[
                    "protocol" => 1,
                    "type" => IRPCProtocol::RESPONSE,
                    "module" => "test_module",
                    "method" => "test_method",
                    "version" => 1,
                    "data" => (object)[]
                ],
                $response->getResponse()
            );
        }

         /**
         * @dataProvider requestProvider
         */
        public function testGetResponse_nullData(RPCRequest $request) : void
        {
            $response = new RPCResponse($request, null);

            $this->assertEquals(
                (object)[
                    "protocol" => 1,
                    "type" => IRPCProtocol::RESPONSE,
                    "module" => "test_module",
                    "method" => "test_method",
                    "version" => 1,
                    "data" => null
                ],
                $response->getResponse()
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
