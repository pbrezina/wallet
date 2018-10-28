<?php declare(strict_types=1);

    namespace Shockie\Tests\Core;

    use InvalidArgumentException;
    use LogicException;
    use PHPUnit\Framework\TestCase;
    use Shockie\Core\SQLExpression;
    use Shockie\Tests\Mocks\Core\SQLFlavorMock;

    final class SQLExpressionTest extends TestCase
    {
        private $flavor;
        private $obj;

        protected function setUp()
        {
            $this->flavor = new SQLFlavorMock();
            $this->obj = new SQLExpression($this->flavor);
        }

        protected function tearDown()
        {
            unset($this->flavor);
            unset($this->obj);
        }

        public function testEmpty() : void
        {
            $this->assertEmpty($this->obj->get());
        }

        public function testCustom_emptyParam() : void
        {
            $this->assertEquals(
                'id = 1',
                $this->obj->custom('id = 1')->get()
            );
        }

        public function testCustom_singleValue() : void
        {
            $this->assertEquals(
                'id = 1',
                $this->obj->custom('id = :id', ['id' => 1])->get()
            );
        }

        public function testCustom_singleColumn() : void
        {
            $this->assertEquals(
                '`id` = 1',
                $this->obj->custom('::id = 1')->get()
            );
        }

        public function testCustom_singleColumnValue() : void
        {
            $this->assertEquals(
                '`id` = 1',
                $this->obj->custom('::id = :id', ['id' => 1])->get()
            );
        }

        public function testCustom_singleColumnValueString() : void
        {
            $this->assertEquals(
                "`id` = 'test'",
                $this->obj->custom('::id = :id', ['id' => 'test'])->get()
            );
        }

        public function testCustom_singleColumnValueNull() : void
        {
            $this->assertEquals(
                "`id` = NULL",
                $this->obj->custom('::id = :id', ['id' => null])->get()
            );
        }

        public function testCustom_complex() : void
        {
            $this->assertEquals(
                "`id` = 1 AND `table`.`name` LIKE 'John%'",
                $this->obj->custom(
                    '::id = :id AND ::table.name LIKE :name',
                    ['id' => 1, 'name' => 'John%']
                )->get()
            );
        }

        public function testBrackets_simpleValid() : void
        {
            $this->assertEquals(
                "()",
                $this->obj->open()->close()->get()
            );
        }

        public function testBrackets_complexValid() : void
        {
            $this->assertEquals(
                "(id = 1)",
                $this->obj->open()->custom('id = 1')->close()->get()
            );
        }

        public function testBrackets_simpleNoClose() : void
        {
            $this->expectException(LogicException::class);
            $this->obj->open()->get();
        }

        public function testBrackets_simpleNoOpen() : void
        {
            $this->expectException(LogicException::class);
            $this->obj->close();
        }

        public function testBrackets_complexNoClose() : void
        {
            $this->expectException(LogicException::class);
            $this->obj->open()->custom('id = 1')->get();
        }

        public function testBrackets_complexNoOpen() : void
        {
            $this->expectException(LogicException::class);
            $this->obj->custom('id = 1')->close();
        }

        public function testNot() : void
        {
            $this->assertEquals(
                'NOT `id` = 1',
                $this->obj->not()->value('id', '=', 1)->get()
            );
        }

        public function testAnd() : void
        {
            $this->assertEquals(
                "`id` = 1 AND `name` = 'John'",
                $this->obj->value('id', '=', 1)->and()->value('name', '=', 'John')->get()
            );
        }

        public function testOr() : void
        {
            $this->assertEquals(
                "`id` = 1 OR `name` = 'John'",
                $this->obj->value('id', '=', 1)->or()->value('name', '=', 'John')->get()
            );
        }

        public function testValue_null() : void
        {
            $this->assertEquals(
                "`id` = NULL",
                $this->obj->value('id', '=', null)->get()
            );
        }

        public function testValue_string() : void
        {
            $this->assertEquals(
                "`id` = 'test'",
                $this->obj->value('id', '=', 'test')->get()
            );
        }

        public function testValue_number() : void
        {
            $this->assertEquals(
                "`id` = 1",
                $this->obj->value('id', '=', 1)->get()
            );
        }

        public function testColumn_equals() : void
        {
            $this->assertEquals(
                "`id` = `other`",
                $this->obj->column('id', '=', 'other')->get()
            );
        }

        public function testColumn_lessThan() : void
        {
            $this->assertEquals(
                "`id` < `other`",
                $this->obj->column('id', '<', 'other')->get()
            );
        }

        public function testBetween() : void
        {
            $this->assertEquals(
                "`id` BETWEEN 1 AND 10",
                $this->obj->between('id', 1, 10)->get()
            );
        }

        public function testIn_one() : void
        {
            $this->assertEquals(
                "`id` IN ('a')",
                $this->obj->in('id', ['a'])->get()
            );
        }

        public function testIn_three() : void
        {
            $this->assertEquals(
                "`id` IN ('a', 'b', 'c')",
                $this->obj->in('id', ['a', 'b', 'c'])->get()
            );
        }

        public function testIn_empty() : void
        {
            $this->expectException(InvalidArgumentException::class);
            $this->obj->in('id', []);
        }

        public function testLike_string() : void
        {
            $this->assertEquals(
                "`id` LIKE 'John%'",
                $this->obj->like('id', 'John%')->get()
            );
        }

        public function testLike_number() : void
        {
            $this->assertEquals(
                "`id` LIKE 1",
                $this->obj->like('id', 1)->get()
            );
        }

        public function testLike_null() : void
        {
            $this->assertEquals(
                "`id` LIKE NULL",
                $this->obj->like('id', null)->get()
            );
        }

        public function testIsNull() : void
        {
            $this->assertEquals(
                "`id` IS NULL",
                $this->obj->isNull('id')->get()
            );
        }

        public function testIsNotNull() : void
        {
            $this->assertEquals(
                "`id` IS NOT NULL",
                $this->obj->isNotNull('id')->get()
            );
        }

        public function testCondition_0arg() : void
        {
            $this->assertEmpty(
                SQLExpression::Condition($this->flavor)
            );
        }

        public function testCondition_1arg_expression() : void
        {
            $expression = new SQLExpression($this->flavor);
            $expression->value('id', '=', 1);

            $this->assertEquals(
                '`id` = 1',
                SQLExpression::Condition($this->flavor, $expression)
            );
        }

        public function testCondition_1arg_custom() : void
        {
            $this->assertEquals(
                'id = 1',
                SQLExpression::Condition($this->flavor, 'id = 1')
            );
        }

        public function testCondition_2arg_custom() : void
        {
            $this->assertEquals(
                '`id` = 1',
                SQLExpression::Condition($this->flavor, '::id = :id', ['id' => 1])
            );
        }

        public function testCondition_3arg_value() : void
        {
            $this->assertEquals(
                '`id` = 1',
                SQLExpression::Condition($this->flavor, 'id', '=', 1)
            );
        }

        public function testCondition_4arg_exception() : void
        {
            $this->expectException(LogicException::class);
            SQLExpression::Condition($this->flavor, 1, 2, 3, 4);
        }
    }
?>
