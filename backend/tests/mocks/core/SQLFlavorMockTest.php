<?php declare(strict_types=1);

    namespace Shockie\Tests\Mocks\Core;

    use PHPUnit\Framework\TestCase;
    use Shockie\Core\SQLResult;
    use Shockie\Tests\Mocks\Core\SQLFlavorMock;

    final class SQLFlavorMockTest extends TestCase
    {
        private $obj;

        protected function setUp()
        {
            $this->obj = new SQLFlavorMock();
        }

        protected function tearDown()
        {
            unset($this->obj);
        }

        public function testTable() : void
        {
            $this->assertEquals(
                '`table`',
                $this->obj->table('table')
            );
        }

        public function testColumn_onlyColumn() : void
        {
            $this->assertEquals(
                '`column`',
                $this->obj->column('column')
            );
        }

        public function testColumn_withTable() : void
        {
            $this->assertEquals(
                '`table`.`column`',
                $this->obj->column('table.column')
            );
        }

        public function testColumn_withDatabase() : void
        {
            $this->assertEquals(
                '`db`.`table`.`column`',
                $this->obj->column('db.table.column')
            );
        }

        public function testColumn_multipleDots() : void
        {
            $this->assertEquals(
                '`db.1`.`table`.`column`',
                $this->obj->column('db.1.table.column')
            );
        }


        public function testValue_null() : void
        {
            $this->assertEquals(
                'NULL',
                $this->obj->value(null)
            );
        }

        public function testValue_string() : void
        {
            $this->assertEquals(
                "'test'",
                $this->obj->value('test')
            );
        }

        public function testValue_emptyString() : void
        {
            $this->assertEquals(
                "''",
                $this->obj->value('')
            );
        }

        public function testValue_number() : void
        {
            $this->assertEquals(
                1,
                $this->obj->value(1)
            );
        }

        public function testRun() : void
        {
            $this->assertInstanceOf(
                SQLResult::class,
                $this->obj->run('SELECT * FROM table LIMIT 1')
            );
        }
    }
?>
