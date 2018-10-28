<?php declare(strict_types=1);

    namespace Shockie\Tests\Core;

    use InvalidArgumentException;
    use LogicException;
    use PHPUnit\Framework\TestCase;
    use Shockie\Core\SQLExpression;
    use Shockie\Core\SQLInnerJoin;
    use Shockie\Core\SQLSelect;
    use Shockie\Core\SQLResult;
    use Shockie\Tests\Mocks\Core\SQLFlavorMock;

    final class SQLSelectTest extends TestCase
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

        public function testAll() : void
        {
            $obj = new SQLSelect($this->flavor);
            $obj->all()->from('table');
            $this->assertEquals(
                'SELECT * FROM `table`',
                $obj->get()
            );
        }

        public function testColumns_empty() : void
        {
            $obj = new SQLSelect($this->flavor);
            $obj->columns()->from('table');
            $this->assertEquals(
                'SELECT * FROM `table`',
                $obj->get()
            );
        }

        public function testColumns_asterisk() : void
        {
            $obj = new SQLSelect($this->flavor);
            $obj->columns('*')->from('table');
            $this->assertEquals(
                'SELECT * FROM `table`',
                $obj->get()
            );
        }

        public function testColumns_column() : void
        {
            $obj = new SQLSelect($this->flavor);
            $obj->columns('a')->from('table');
            $this->assertEquals(
                'SELECT `a` FROM `table`',
                $obj->get()
            );
        }

        public function testColumns_tableAndColumn() : void
        {
            $obj = new SQLSelect($this->flavor);
            $obj->columns('table.column')->from('table');
            $this->assertEquals(
                'SELECT `table`.`column` FROM `table`',
                $obj->get()
            );
        }

        public function testColumns_arrayNoAlias() : void
        {
            $obj = new SQLSelect($this->flavor);
            $obj->columns(['a', 'table.b'])->from('table');
            $this->assertEquals(
                'SELECT `a`, `table`.`b` FROM `table`',
                $obj->get()
            );
        }

        public function testColumns_arrayAlias() : void
        {
            $obj = new SQLSelect($this->flavor);
            $obj->columns(['a1' => 'a', 'a2' => 'table.b'])->from('table');
            $this->assertEquals(
                'SELECT `a` AS `a1`, `table`.`b` AS `a2` FROM `table`',
                $obj->get()
            );
        }

        public function testColumns_arrayMisc() : void
        {
            $obj = new SQLSelect($this->flavor);
            $obj->columns(['a1' => 'a', 'b'])->from('table');
            $this->assertEquals(
                'SELECT `a` AS `a1`, `b` FROM `table`',
                $obj->get()
            );
        }

        public function testColumns_arrayExpression() : void
        {
            $expression = new SQLExpression($this->flavor);
            $expression->custom('COUNT(::a)');
            $obj = new SQLSelect($this->flavor);
            $obj->columns(['a1' => $expression])->from('table');
            $this->assertEquals(
                'SELECT COUNT(`a`) AS `a1` FROM `table`',
                $obj->get()
            );
        }

        public function testColumns_complex() : void
        {
            $expression = new SQLExpression($this->flavor);
            $expression->custom('COUNT(::a)');
            $obj = new SQLSelect($this->flavor);
            $obj->columns(
                '*', 'a', 'b',
                ['a1' => 'c', 'a2' => 'table.d'],
                ['a3' => $expression]
            )->from('table');
            $this->assertEquals(
                'SELECT *, `a`, `b`, `c` AS `a1`, `table`.`d` AS `a2`, COUNT(`a`) AS `a3` FROM `table`',
                $obj->get()
            );
        }

        public function testFrom_table() : void
        {
            $obj = new SQLSelect($this->flavor);
            $obj->all()->from('table');
            $this->assertEquals(
                'SELECT * FROM `table`',
                $obj->get()
            );
        }

        public function testFrom_join() : void
        {
            $join = new SQLInnerJoin($this->flavor, 'left', 'right');
            $obj = new SQLSelect($this->flavor);
            $obj->all()->from($join);
            $this->assertEquals(
                'SELECT * FROM `left` INNER JOIN `right`',
                $obj->get()
            );
        }

        public function testFrom_tableAlias() : void
        {
            $obj = new SQLSelect($this->flavor);
            $obj->all()->from(['t' => 'table']);
            $this->assertEquals(
                'SELECT * FROM `table` AS `t`',
                $obj->get()
            );
        }

        public function testFrom_select() : void
        {
            $select = new SQLSelect($this->flavor);
            $select->all()->from('table');

            $obj = new SQLSelect($this->flavor);
            $obj->all()->from(['t' => $select]);
            $this->assertEquals(
                'SELECT * FROM (SELECT * FROM `table`) AS `t`',
                $obj->get()
            );
        }

        public function testWhere() : void
        {
            $obj = new SQLSelect($this->flavor);
            $obj->all()->from('table')->where('::id = :id', ['id' => 1]);
            $this->assertEquals(
                'SELECT * FROM `table` WHERE `id` = 1',
                $obj->get()
            );
        }

        public function testGroupBy_column() : void
        {
            $obj = new SQLSelect($this->flavor);
            $obj->all()->from('table')->groupby('a');
            $this->assertEquals(
                'SELECT * FROM `table` GROUP BY `a`',
                $obj->get()
            );
        }

        public function testGroupBy_tableColumn() : void
        {
            $obj = new SQLSelect($this->flavor);
            $obj->all()->from('table')->groupby('table.a');
            $this->assertEquals(
                'SELECT * FROM `table` GROUP BY `table`.`a`',
                $obj->get()
            );
        }

        public function testGroupBy_expression() : void
        {
            $expression = new SQLExpression($this->flavor);
            $expression->custom('::a + ::b');
            $obj = new SQLSelect($this->flavor);
            $obj->all()->from('table')->groupby($expression);
            $this->assertEquals(
                'SELECT * FROM `table` GROUP BY `a` + `b`',
                $obj->get()
            );
        }

        public function testHaving() : void
        {
            $obj = new SQLSelect($this->flavor);
            $obj->all()->from('table')->having('COUNT(::a) < 5');
            $this->assertEquals(
                'SELECT * FROM `table` HAVING COUNT(`a`) < 5',
                $obj->get()
            );
        }

        public function testOrderBy_column() : void
        {
            $obj = new SQLSelect($this->flavor);
            $obj->all()->from('table')->orderby('a');
            $this->assertEquals(
                'SELECT * FROM `table` ORDER BY `a` ASC',
                $obj->get()
            );
        }

        public function testOrderBy_tableAndColumn() : void
        {
            $obj = new SQLSelect($this->flavor);
            $obj->all()->from('table')->orderby('table.a');
            $this->assertEquals(
                'SELECT * FROM `table` ORDER BY `table`.`a` ASC',
                $obj->get()
            );
        }

        public function testOrderBy_expression() : void
        {
            $expression = new SQLExpression($this->flavor);
            $expression->custom('::a + ::b');
            $obj = new SQLSelect($this->flavor);
            $obj->all()->from('table')->orderby($expression);
            $this->assertEquals(
                'SELECT * FROM `table` ORDER BY `a` + `b` ASC',
                $obj->get()
            );
        }

        public function testOrderBy_column_asc() : void
        {
            $obj = new SQLSelect($this->flavor);
            $obj->all()->from('table')->orderby(['a', 'ASC']);
            $this->assertEquals(
                'SELECT * FROM `table` ORDER BY `a` ASC',
                $obj->get()
            );
        }

        public function testOrderBy_column_desc() : void
        {
            $obj = new SQLSelect($this->flavor);
            $obj->all()->from('table')->orderby(['a', 'DESC']);
            $this->assertEquals(
                'SELECT * FROM `table` ORDER BY `a` DESC',
                $obj->get()
            );
        }

        public function testOrderBy_tableAndColumn_asc() : void
        {
            $obj = new SQLSelect($this->flavor);
            $obj->all()->from('table')->orderby(['table.a', 'ASC']);
            $this->assertEquals(
                'SELECT * FROM `table` ORDER BY `table`.`a` ASC',
                $obj->get()
            );
        }

        public function testOrderBy_tableAndColumn_desc() : void
        {
            $obj = new SQLSelect($this->flavor);
            $obj->all()->from('table')->orderby(['table.a', 'DESC']);
            $this->assertEquals(
                'SELECT * FROM `table` ORDER BY `table`.`a` DESC',
                $obj->get()
            );
        }

        public function testOrderBy_expression_asc() : void
        {
            $expression = new SQLExpression($this->flavor);
            $expression->custom('::a + ::b');
            $obj = new SQLSelect($this->flavor);
            $obj->all()->from('table')->orderby([$expression, 'ASC']);
            $this->assertEquals(
                'SELECT * FROM `table` ORDER BY `a` + `b` ASC',
                $obj->get()
            );
        }

        public function testOrderBy_expression_desc() : void
        {
            $expression = new SQLExpression($this->flavor);
            $expression->custom('::a + ::b');
            $obj = new SQLSelect($this->flavor);
            $obj->all()->from('table')->orderby([$expression, 'DESC']);
            $this->assertEquals(
                'SELECT * FROM `table` ORDER BY `a` + `b` DESC',
                $obj->get()
            );
        }

        public function testOrderBy_invalid() : void
        {
            $this->expectException(InvalidArgumentException::class);
            $obj = new SQLSelect($this->flavor);
            $obj->all()->from('table')->orderby(['a', 'ASCC']);
        }

        public function testOrderBy_complex() : void
        {
            $expression = new SQLExpression($this->flavor);
            $expression->custom('::a + ::b');
            $obj = new SQLSelect($this->flavor);
            $obj->all()->from('table')->orderby('a', 'b', [$expression, 'DESC']);
            $this->assertEquals(
                'SELECT * FROM `table` ORDER BY `a` ASC, `b` ASC, `a` + `b` DESC',
                $obj->get()
            );
        }

        public function testLimit_simple() : void
        {
            $obj = new SQLSelect($this->flavor);
            $obj->all()->from('table')->limit(5);
            $this->assertEquals(
                'SELECT * FROM `table` LIMIT 5',
                $obj->get()
            );
        }

        public function testLimit_offset() : void
        {
            $obj = new SQLSelect($this->flavor);
            $obj->all()->from('table')->limit(5, 10);
            $this->assertEquals(
                'SELECT * FROM `table` LIMIT 5 OFFSET 10',
                $obj->get()
            );
        }

        public function testGet_emptyFrom() : void
        {
            $this->expectException(LogicException::class);
            $obj = new SQLSelect($this->flavor);
            $obj->get();
        }

        public function testGet_emptyColumns() : void
        {
            $obj = new SQLSelect($this->flavor);
            $obj->from('table');
            $this->assertEquals(
                'SELECT * FROM `table`',
                $obj->get()
            );
        }

        public function testGet_all() : void
        {
            $obj = new SQLSelect($this->flavor);
            $obj->columns('a')
                ->from('table')
                ->where('::a = 5')
                ->groupby('a')
                ->having('COUNT(::b) < 5')
                ->orderby('a')
                ->limit(5, 10);

            $this->assertEquals(
                'SELECT `a` FROM `table` WHERE `a` = 5 GROUP BY `a` HAVING COUNT(`b`) < 5 ORDER BY `a` ASC LIMIT 5 OFFSET 10',
                $obj->get()
            );
        }

        public function testRun() : void
        {
            $obj = new SQLSelect($this->flavor);
            $obj->all()->from('table');
            $this->assertInstanceOf(
                SQLResult::class,
                $obj->run()
            );
        }
    }
?>
