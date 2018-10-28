<?php declare(strict_types=1);

    namespace Shockie\Tests\Core;

    use PHPUnit\Framework\TestCase;
    use Shockie\Core\RPCRequest;
    use Shockie\Core\RPCRequestData;
    use Shockie\Core\JSON;
    use Shockie\Interfaces\IRPCProtocol;
    use Shockie\Interfaces\IRPCRequest;

    final class RPCRequestDataTest extends TestCase
    {
        private $data;
        private $request;

        protected function setUp()
        {
            $json = '{
                "protocol" : 1,
                "type" : "request",
                "module" : "test_module",
                "method" : "test_method",
                "version" : 1,
                "data" : {"test" : true}
            }';

            $this->request = new RPCRequest(JSON::Parse($json));
            $this->data = new RPCRequestData($this->request);
        }

        protected function tearDown()
        {
            unset($this->data);
        }

        public function testGetRequest() : void
        {
            $this->assertSame(
                $this->data->getRequest(),
                $this->request
            );
        }

        public function testGetMethodName() : void
        {
            $this->assertEquals(
                $this->data->getMethodName(),
                'test_method'
            );
        }

        public function testGetMethodVersion() : void
        {
            $this->assertEquals(
                $this->data->getMethodVersion(),
                1
            );
        }

        public function testGetData() : void
        {
            $this->assertSame(
                $this->data->getData(),
                $this->request->getData()
            );
        }
    }
?>
