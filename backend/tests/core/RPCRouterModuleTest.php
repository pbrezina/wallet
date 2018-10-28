<?php declare(strict_types=1);

    namespace Shockie\Tests\Core;

    use PHPUnit\Framework\TestCase;
    use Shockie\Core\RPCRouterModule;
    use Shockie\Exceptions\RPCDisabledModuleException;
    use Shockie\Exceptions\FileNotFoundException;
    use Shockie\Exceptions\ClassNotFoundException;
    use Shockie\Tests\Mocks\Core\RPCModule_BaseClass;

    final class RPCRouterModuleTest extends TestCase
    {
        public function testLoad_enabled() : void
        {
            $module = new RPCRouterModule(
                'test_module',
                './tests/mocks/core/RPCModule_BaseClass.php',
                'Shockie\Tests\Mocks\Core\RPCModule_BaseClass',
                false
            );

            $this->expectException(RPCDisabledModuleException::class);
            $module->load();
        }

        public function testLoad_file_not_found() : void
        {
            $module = new RPCRouterModule(
                'test_module',
                './tests/mocks/core/RPCModule_BaseClass.php.notfound',
                'Shockie\Tests\Mocks\Core\RPCModule_BaseClass',
                true
            );

            $this->expectException(FileNotFoundException::class);
            $module->load();
        }

        public function testLoad_class_not_found() : void
        {
            $module = new RPCRouterModule(
                'test_module',
                './tests/mocks/core/RPCModule_BaseClass.php',
                'Shockie\Tests\Mocks\Core\RPCModule_BaseClassNotFound',
                true
            );

            $this->expectException(ClassNotFoundException::class);
            $module->load();
        }

        public function testLoad_success() : void
        {
            $module = new RPCRouterModule(
                'test_module',
                './tests/mocks/core/RPCModule_BaseClass.php',
                'Shockie\Tests\Mocks\Core\RPCModule_BaseClass',
                true
            );

            $module->load();
            $this->addToAssertionCount(1);
        }

        public function testCreate() : void
        {
            $module = new RPCRouterModule(
                'test_module',
                './tests/mocks/core/RPCModule_BaseClass.php',
                'Shockie\Tests\Mocks\Core\RPCModule_BaseClass',
                true
            );

            $module->load();
            $obj = $module->create();

            $this->assertInstanceOf(RPCModule_BaseClass::class, $obj);
        }
    }
?>
