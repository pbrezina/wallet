<?php declare(strict_types=1);

    namespace Shockie\Tests\Core;

    use InvalidArgumentException;
    use LogicException;
    use PHPUnit\Framework\TestCase;
    use Shockie\Core\SQLDelete;
    use Shockie\Core\SQLResult;
    use Shockie\Tests\Mocks\Core\SQLFlavorMock;

    final class SQLDeleteTest extends TestCase
    {
        private $flavor;

        protected function setUp()
        {
            $this->flavor = new SQLFlavorMock();
        }

        protected function tearDown()
        {
            unset($this->flavor);
        }

        public function testConstruct_emptyTable() : void
        {
            $this->expectException(InvalidArgumentException::class);

            $obj = new SQLDelete($this->flavor, '');
        }

        public function testGet_missingRequiredCondition() : void
        {
            $this->expectException(LogicException::class);

            $obj = new SQLDelete($this->flavor, 'table');
            $obj->get();
        }

        public function testGet_withoutCondition() : void
        {
            $obj = new SQLDelete($this->flavor, 'table', true);
            $this->assertEquals(
                'DELETE FROM `table`',
                $obj->get()
            );
        }

        public function testGet_withCondition() : void
        {
            $obj = new SQLDelete($this->flavor, 'table');
            $obj->where('id = 1');
            $this->assertEquals(
                'DELETE FROM `table` WHERE id = 1',
                $obj->get()
            );
        }

        public function testRun() : void
        {
            $obj = new SQLDelete($this->flavor, 'table');
            $obj->where('id = 1');
            $this->assertInstanceOf(
                SQLResult::class,
                $obj->run()
            );
        }
    }
?>
