<?php declare(strict_types=1);

    namespace Shockie\Tests\Core;

    use InvalidArgumentException;
    use LogicException;
    use PHPUnit\Framework\TestCase;
    use Shockie\Core\SQLExpression;
    use Shockie\Core\SQLUpdate;
    use Shockie\Core\SQLResult;
    use Shockie\Tests\Mocks\Core\SQLFlavorMock;

    final class SQLUpdateTest extends TestCase
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

            $obj = new SQLUpdate($this->flavor, '');
        }

        public function testGet_missingRequiredCondition() : void
        {
            $this->expectException(LogicException::class);

            $obj = new SQLUpdate($this->flavor, 'table');
            $obj->get();
        }

        public function testGet_missingValues() : void
        {
            $this->expectException(LogicException::class);

            $obj = new SQLUpdate($this->flavor, 'table');
            $obj->where('id = 1');
            $obj->get();
        }

        public function testGet_emptyValues() : void
        {
            $this->expectException(LogicException::class);

            $obj = new SQLUpdate($this->flavor, 'table');
            $obj->set([]);
            $obj->where('id = 1');
            $obj->get();
        }

        public function testGet_basicNull() : void
        {
            $obj = new SQLUpdate($this->flavor, 'table', true);
            $obj->set(['test' => null]);
            $this->assertEquals(
                'UPDATE `table` SET `test` = NULL',
                $obj->get()
            );
        }

        public function testGet_basicString() : void
        {
            $obj = new SQLUpdate($this->flavor, 'table', true);
            $obj->set(['test' => 'value']);
            $this->assertEquals(
                "UPDATE `table` SET `test` = 'value'",
                $obj->get()
            );
        }

        public function testGet_basicNumber() : void
        {
            $obj = new SQLUpdate($this->flavor, 'table', true);
            $obj->set(['test' => 1]);
            $this->assertEquals(
                "UPDATE `table` SET `test` = 1",
                $obj->get()
            );
        }

        public function testGet_basicExpression() : void
        {
            $expression = new SQLExpression($this->flavor);
            $expression->custom('FUNC(::a + ::b)');
            $obj = new SQLUpdate($this->flavor, 'table', true);
            $obj->set(['test' => $expression]);
            $this->assertEquals(
                "UPDATE `table` SET `test` = FUNC(`a` + `b`)",
                $obj->get()
            );
        }

        public function testGet_complexSet() : void
        {
            $expression = new SQLExpression($this->flavor);
            $expression->custom('FUNC(::a + ::b)');
            $obj = new SQLUpdate($this->flavor, 'table', true);
            $obj->set([
                'a' => 1,
                'b' => '2',
                'c' => null,
                'd' => $expression
            ]);
            $this->assertEquals(
                "UPDATE `table` SET `a` = 1, `b` = '2', `c` = NULL, `d` = FUNC(`a` + `b`)",
                $obj->get()
            );
        }

        public function testGet_withCondition() : void
        {
            $obj = new SQLUpdate($this->flavor, 'table');
            $obj->set(['test' => 1]);
            $obj->where('id = 1');
            $this->assertEquals(
                'UPDATE `table` SET `test` = 1 WHERE id = 1',
                $obj->get()
            );
        }

        public function testRun() : void
        {
            $obj = new SQLUpdate($this->flavor, 'table');
            $obj->set(['test' => 1]);
            $obj->where('id = 1');
            $this->assertInstanceOf(
                SQLResult::class,
                $obj->run()
            );
        }
    }
?>
