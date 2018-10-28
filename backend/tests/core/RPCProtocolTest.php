<?php declare(strict_types=1);

    namespace Shockie\Tests\Core;

    use PHPUnit\Framework\TestCase;
    use Shockie\Core\RPCProtocol;
    use Shockie\Tests\Mocks\Core\RPCProtocol_BaseClass;

    final class RPCProtocolTest extends TestCase
    {
        private $protocol;

        protected function setUp()
        {
            $this->protocol = new RPCProtocol_BaseClass(
                1, 'test_type', 'test_module', 'test_method', 2
            );
        }

        protected function tearDown()
        {
            unset($this->protocol);
        }

        public function testGetProtocolVersion() : void
        {
            $this->assertEquals(
                1,
                $this->protocol->getProtocolVersion()
            );
        }

        public function testGetType() : void
        {
            $this->assertEquals(
                'test_type',
                $this->protocol->getType()
            );
        }

        public function testGetModuleName() : void
        {
            $this->assertEquals(
                'test_module',
                $this->protocol->getModuleName()
            );
        }

        public function testGetMethodName() : void
        {
            $this->assertEquals(
                'test_method',
                $this->protocol->getMethodName()
            );
        }

        public function testGetMethodVersion() : void
        {
            $this->assertEquals(
                2,
                $this->protocol->getMethodVersion()
            );
        }

        public function testGetAsObject() : void
        {
            $this->assertEquals(
                (object)[
                    'protocol' => 1,
                    'type' => 'test_type',
                    'module' => 'test_module',
                    'method' => 'test_method',
                    'version' => 2
                ],
                $this->protocol->getAsObject()
            );
        }
    }
?>
