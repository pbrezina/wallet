<?php declare(strict_types=1);

    namespace Shockie\Tests\Core;

    use LogicException;
    use PHPUnit\Framework\TestCase;
    use Shockie\Tests\Mocks\Core\SQLFlavorMock;

    final class SQLDatabaseTest extends TestCase
    {
        private $db;

        protected function setUp()
        {
            $this->db = new SQLFlavorMock();
        }

        protected function tearDown()
        {
            unset($this->db);
        }

        public function testExpression_empty() : void
        {
            $this->assertEquals(
                '',
                $this->db->expression()->get()
            );
        }

        public function testExpression_custom() : void
        {
            $this->assertEquals(
                '`id` = 1',
                $this->db->expression('::id = :id', ['id' => 1])->get()
            );
        }

        public function testInnerJoin() : void
        {
            $this->assertEquals(
                '`left` INNER JOIN `right`',
                $this->db->innerJoin('left', 'right')->get()
            );
        }

        public function testLeftOuterJoin() : void
        {
            $this->assertEquals(
                '`left` LEFT OUTER JOIN `right`',
                $this->db->leftOuterJoin('left', 'right')->get()
            );
        }

        public function testRightOuterJoin() : void
        {
            $this->assertEquals(
                '`left` RIGHT OUTER JOIN `right`',
                $this->db->rightOuterJoin('left', 'right')->get()
            );
        }

        public function testSelect_empty() : void
        {
            $obj = $this->db->select()->from('table');
            $this->assertEquals(
                'SELECT * FROM `table`',
                $obj->get()
            );
        }

        public function testSelect_asterisk() : void
        {
            $obj = $this->db->select('*')->from('table');
            $this->assertEquals(
                'SELECT * FROM `table`',
                $obj->get()
            );
        }
        public function testSelect_columns() : void
        {
            $obj = $this->db->select('a', 'b')->from('table');
            $this->assertEquals(
                'SELECT `a`, `b` FROM `table`',
                $obj->get()
            );
        }

        public function testInsertInto() : void
        {
            $obj = $this->db->insertInto('table')->set(['a' => 1]);
            $this->assertEquals(
                'INSERT INTO `table` (`a`) VALUES (1)',
                $obj->get()
            );
        }

        public function testUpdate_withoutCondition() : void
        {
            $obj = $this->db->update('table')->set(['a' => 'b']);
            $this->expectException(LogicException::class);
            $obj->get();
        }

        public function testUpdate_withCondition() : void
        {
            $obj = $this->db->update('table')->set(['a' => 'b'])->where('id = 1');
            $this->assertEquals(
                "UPDATE `table` SET `a` = 'b' WHERE id = 1",
                $obj->get()
            );
        }

        public function testUpdate_unconditional() : void
        {
            $obj = $this->db->update('table', true)->set(['a' => 'b']);
            $this->assertEquals(
                "UPDATE `table` SET `a` = 'b'",
                $obj->get()
            );
        }

        public function testDelete_withoutCondition() : void
        {
            $obj = $this->db->delete('table');
            $this->expectException(LogicException::class);
            $obj->get();
        }

        public function testDelete_withCondition() : void
        {
            $obj = $this->db->delete('table')->where('id = 1');
            $this->assertEquals(
                "DELETE FROM `table` WHERE id = 1",
                $obj->get()
            );
        }

        public function testDelete_unconditional() : void
        {
            $obj = $this->db->delete('table', true);
            $this->assertEquals(
                "DELETE FROM `table`",
                $obj->get()
            );
        }

        public function testSelectById_default() : void
        {
            $obj = $this->db->selectById('table', 1);
            $this->assertEquals(
                "SELECT * FROM `table` WHERE `id` = 1 LIMIT 1",
                $obj->get()
            );
        }

        public function testSelectById_custom() : void
        {
            $obj = $this->db->selectById('table', 1, 'user_id');
            $this->assertEquals(
                "SELECT * FROM `table` WHERE `user_id` = 1 LIMIT 1",
                $obj->get()
            );
        }

        public function testUpdateById_default() : void
        {
            $obj = $this->db->updateById('table', 1)->set(['a' => 1]);
            $this->assertEquals(
                "UPDATE `table` SET `a` = 1 WHERE `id` = 1",
                $obj->get()
            );
        }

        public function testUpdateById_custom() : void
        {
            $obj = $this->db->updateById('table', 1, 'user_id')->set(['a' => 1]);
            $this->assertEquals(
                "UPDATE `table` SET `a` = 1 WHERE `user_id` = 1",
                $obj->get()
            );
        }

        public function testDeleteById_default() : void
        {
            $obj = $this->db->deleteById('table', 1);
            $this->assertEquals(
                "DELETE FROM `table` WHERE `id` = 1",
                $obj->get()
            );
        }

        public function testDeleteById_custom() : void
        {
            $obj = $this->db->deleteById('table', 1, 'user_id');
            $this->assertEquals(
                "DELETE FROM `table` WHERE `user_id` = 1",
                $obj->get()
            );
        }

        public function testPrepareQueryColumns_outside_quotes() : void
        {
            $this->assertEquals(
                '`id` = 1',
                $this->db->prepareQueryColumns('::id = 1')
            );
        }

        public function testPrepareQueryColumns_inside_quotes() : void
        {
            $this->assertEquals(
                "`id` = 1 AND `test` = '::test'",
                $this->db->prepareQueryColumns("::id = 1 AND ::test = '::test'")
            );
        }

        public function testPrepareQueryValues_numeric() : void
        {
            $this->assertEquals(
                "id = 'test'",
                $this->db->prepareQueryValues('id = :0', ['test'])
            );
        }

        public function testPrepareQueryValues_outside_quotes() : void
        {
            $this->assertEquals(
                "id = 'test'",
                $this->db->prepareQueryValues('id = :id', ['id' => 'test'])
            );
        }


        public function testPrepareQueryValues_inside_quotes() : void
        {
            $this->assertEquals(
                "id = 'test' AND test = ':test'",
                $this->db->prepareQueryValues(
                    "id = :id AND test = ':test'",
                    ['id' => 'test']
                )
            );
        }

        public function testPrepareQueryValues_missing_parameter() : void
        {
            $this->expectException(LogicException::class);
            $this->db->prepareQueryValues("id = :id");
        }

        public function testPrepareQueryString_outside_quotes() : void
        {
            $this->assertEquals(
                "`id` = 'test'",
                $this->db->prepareQueryString('::id = :id', ['id' => 'test'])
            );
        }

        public function testPrepareQueryString_inside_quotes() : void
        {
            $this->assertEquals(
                "`id` = 'test' AND `test` = '::test :id'",
                $this->db->prepareQueryString(
                    "::id = :id AND ::test = '::test :id'",
                    ['id' => 'test']
                )
            );
        }

        public function testPrepareQueryString_missing_parameter() : void
        {
            $this->expectException(LogicException::class);
            $this->db->prepareQueryString("id = :id");
        }
    }
?>
