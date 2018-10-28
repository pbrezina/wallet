<?php declare(strict_types=1);

    namespace Shockie\Tests\Core;

    use PHPUnit\Framework\TestCase;
    use Shockie\Core\JSON;
    use Shockie\Core\RPCError;
    use Shockie\Core\RPCResponse;
    use Shockie\Core\RPCRouter;

    final class RPCRouterTest extends TestCase
    {
        private $router;

        protected function setUp()
        {
            $this->router = new RPCRouter();
            $this->router->register(
                'test_module',
                './tests/mocks/core/RPCModule_BaseClass.php',
                'Shockie\Tests\Mocks\Core\RPCModule_BaseClass',
                true
            );
        }

        protected function tearDown()
        {
            unset($this->router);
        }

        public function testProcess_ok() : void
        {
            $request = JSON::Parse('{
                "protocol" : 1,
                "type" : "request",
                "module" : "test_module",
                "method" : "test_method",
                "version" : 1,
                "data" : {"test" : true}
            }');

            $response = $this->router->process($request);
            $this->assertInstanceOf(RPCResponse::class, $response);
            $this->assertEquals(
                (object)[
                    "protocol" => 1,
                    "type" => "response",
                    "module" => "test_module",
                    "method" => "test_method",
                    "version" => 1,
                    "data" => "this is a test"
                ],
                $response->getResponse()
            );
        }

        public function testProcess_invalid_request() : void
        {
            $request = JSON::Parse('{
                "protocol" : 1,
                "type" : "response",
                "module" : "test_module",
                "method" : "test_method",
                "version" : 1,
                "data" : {"test" : true}
            }');

            $response = $this->router->process($request);
            $this->assertInstanceOf(RPCError::class, $response);

            /* We will not check error data as it is already
             * tested in RPCError. */
            $data = $response->getResponse();
            $data->data = null;

            $this->assertEquals(
                (object)[
                    "protocol" => 1,
                    "type" => "error",
                    "module" => "(unknown)",
                    "method" => "(unknown)",
                    "version" => 1,
                    "data" => null
                ],
                $data
            );
        }

        public function testProcess_unknown_module() : void
        {
            $request = JSON::Parse('{
                "protocol" : 1,
                "type" : "request",
                "module" : "unknown",
                "method" : "test_method",
                "version" : 1,
                "data" : {"test" : true}
            }');

            $response = $this->router->process($request);
            $this->assertInstanceOf(RPCError::class, $response);

            /* We will not check error data as it is already
             * tested in RPCError. */
            $data = $response->getResponse();
            $data->data = null;

            $this->assertEquals(
                (object)[
                    "protocol" => 1,
                    "type" => "error",
                    "module" => "unknown",
                    "method" => "test_method",
                    "version" => 1,
                    "data" => null
                ],
                $data
            );
        }

        public function testProcess_module_exception() : void
        {
            $request = JSON::Parse('{
                "protocol" : 1,
                "type" : "request",
                "module" : "test_module",
                "method" : "test_exception",
                "version" : 1,
                "data" : {"test" : true}
            }');

            $response = $this->router->process($request);
            $this->assertInstanceOf(RPCError::class, $response);

            /* We will not check error data as it is already
             * tested in RPCError. */
            $data = $response->getResponse();
            $data->data = null;

            $this->assertEquals(
                (object)[
                    "protocol" => 1,
                    "type" => "error",
                    "module" => "test_module",
                    "method" => "test_exception",
                    "version" => 1,
                    "data" => null
                ],
                $data
            );
        }
    }
?>
