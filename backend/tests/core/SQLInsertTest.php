<?php declare(strict_types=1);

    namespace Shockie\Tests\Core;

    use InvalidArgumentException;
    use LogicException;
    use PHPUnit\Framework\TestCase;
    use Shockie\Core\SQLExpression;
    use Shockie\Core\SQLInsert;
    use Shockie\Core\SQLResult;
    use Shockie\Tests\Mocks\Core\SQLFlavorMock;

    final class SQLInsertTest extends TestCase
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

            $obj = new SQLInsert($this->flavor, '');
        }

        public function testGet_missingValues() : void
        {
            $this->expectException(LogicException::class);

            $obj = new SQLInsert($this->flavor, 'table');
            $obj->get();
        }

        public function testGet_emptyValues() : void
        {
            $this->expectException(LogicException::class);

            $obj = new SQLInsert($this->flavor, 'table');
            $obj->set([]);
            $obj->get();
        }

        public function testGet_basicNull() : void
        {
            $obj = new SQLInsert($this->flavor, 'table');
            $obj->set(['test' => null]);
            $this->assertEquals(
                'INSERT INTO `table` (`test`) VALUES (NULL)',
                $obj->get()
            );
        }

        public function testGet_basicString() : void
        {
            $obj = new SQLInsert($this->flavor, 'table');
            $obj->set(['test' => 'value']);
            $this->assertEquals(
                "INSERT INTO `table` (`test`) VALUES ('value')",
                $obj->get()
            );
        }

        public function testGet_basicNumber() : void
        {
            $obj = new SQLInsert($this->flavor, 'table');
            $obj->set(['test' => 1]);
            $this->assertEquals(
                'INSERT INTO `table` (`test`) VALUES (1)',
                $obj->get()
            );
        }

        public function testGet_basicExpression() : void
        {
            $expression = new SQLExpression($this->flavor);
            $expression->custom('FUNC(::a + ::b)');
            $obj = new SQLInsert($this->flavor, 'table');
            $obj->set(['test' => $expression]);
            $this->assertEquals(
                'INSERT INTO `table` (`test`) VALUES (FUNC(`a` + `b`))',
                $obj->get()
            );
        }

        public function testGet_complexSet() : void
        {
            $expression = new SQLExpression($this->flavor);
            $expression->custom('FUNC(::a + ::b)');
            $obj = new SQLInsert($this->flavor, 'table');
            $obj->set([
                'a' => 1,
                'b' => '2',
                'c' => null,
                'd' => $expression
            ]);
            $this->assertEquals(
                "INSERT INTO `table` (`a`, `b`, `c`, `d`) VALUES (1, '2', NULL, FUNC(`a` + `b`))",
                $obj->get()
            );
        }

        public function testRun() : void
        {
            $obj = new SQLInsert($this->flavor, 'table');
            $obj->set(['test' => 1]);
            $this->assertInstanceOf(
                SQLResult::class,
                $obj->run()
            );
        }
    }
?>
