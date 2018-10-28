<?php declare(strict_types=1);

    namespace Shockie\Tests\Core;

    use Throwable;
    use PHPUnit\Framework\TestCase;
    use Shockie\Core\RPCError;
    use Shockie\Core\RPCRequest;
    use Shockie\Core\JSON;
    use Shockie\Exceptions\FileNotFoundException;
    use Shockie\Exceptions\IOException;
    use Shockie\Interfaces\IRPCProtocol;

    final class RPCErrorTest extends TestCase
    {
        /**
         * @dataProvider requestProvider
         */
        public function testGetResponse_single_exception(RPCRequest $request) : void
        {
            try {
                throw new IOException('Test Exception');
            } catch (Throwable $e) {
                $error = new RPCError($request, $e);
            }

            $data = $error->getResponse();

            $this->assertObjectHasAttribute('protocol', $data);
            $this->assertObjectHasAttribute('type', $data);
            $this->assertObjectHasAttribute('module', $data);
            $this->assertObjectHasAttribute('method', $data);
            $this->assertObjectHasAttribute('version', $data);
            $this->assertObjectHasAttribute('data', $data);
            $this->assertObjectHasAttribute('errors', $data->data);
            $this->assertObjectHasAttribute('backtrace', $data->data);

            $this->assertEquals(1, $data->protocol);
            $this->assertEquals(IRPCProtocol::ERROR, $data->type);
            $this->assertEquals('test_module', $data->module);
            $this->assertEquals('test_method', $data->method);
            $this->assertEquals(1, $data->version);

            $this->assertCount(1, $data->data->errors);
            $this->assertInternalType('array', $data->data->errors);
            $this->assertInternalType('array', $data->data->backtrace);

            $this->assertEquals(
                [
                    (object)[
                        "class" => get_class($e),
                        "message" => $e->getMessage(),
                        "code" => $e->getCode(),
                        "file" => $e->getFile(),
                        "line" => $e->getLine()
                    ]
                ],
                $data->data->errors
            );

            /* We do not test backtrace as it would required the same
             * code that is used in generator, because it depends on
             * phpunit internals.
             */
        }

        /**
         * @dataProvider requestProvider
         */
        public function testGetResponse_two_exceptions(RPCRequest $request) : void
        {
            try {
                try {
                    throw new IOException('Test Exception');
                } catch (Throwable $e1) {
                    throw new FileNotFoundException('file', $e1);
                }
            } catch (Throwable $e2) {
                $error = new RPCError($request, $e2);
            }

            $data = $error->getResponse();

            $this->assertObjectHasAttribute('protocol', $data);
            $this->assertObjectHasAttribute('type', $data);
            $this->assertObjectHasAttribute('module', $data);
            $this->assertObjectHasAttribute('method', $data);
            $this->assertObjectHasAttribute('version', $data);
            $this->assertObjectHasAttribute('data', $data);
            $this->assertObjectHasAttribute('errors', $data->data);
            $this->assertObjectHasAttribute('backtrace', $data->data);

            $this->assertEquals(1, $data->protocol);
            $this->assertEquals(IRPCProtocol::ERROR, $data->type);
            $this->assertEquals('test_module', $data->module);
            $this->assertEquals('test_method', $data->method);
            $this->assertEquals(1, $data->version);

            $this->assertCount(2, $data->data->errors);
            $this->assertInternalType('array', $data->data->errors);
            $this->assertInternalType('array', $data->data->backtrace);

            $this->assertEquals(
                [
                    (object)[
                        "class" => get_class($e1),
                        "message" => $e1->getMessage(),
                        "code" => $e1->getCode(),
                        "file" => $e1->getFile(),
                        "line" => $e1->getLine()
                    ],
                    (object)[
                        "class" => get_class($e2),
                        "message" => $e2->getMessage(),
                        "code" => $e2->getCode(),
                        "file" => $e2->getFile(),
                        "line" => $e2->getLine()
                    ]
                ],
                $data->data->errors
            );

            /* We do not test backtrace as it would required the same
             * code that is used in generator, because it depends on
             * phpunit internals.
             */
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
