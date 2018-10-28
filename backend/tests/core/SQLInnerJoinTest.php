<?php declare(strict_types=1);

    namespace Shockie\Tests\Core;

    use PHPUnit\Framework\TestCase;
    use Shockie\Core\SQLInnerJoin;
    use Shockie\Core\SQLSelect;
    use Shockie\Tests\Mocks\Core\SQLFlavorMock;

    final class SQLInnerJoinTest extends TestCase
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

        public function testGet_bothTableNoCondition() : void
        {
            $obj = new SQLInnerJoin($this->flavor, 'left', 'right');
            $this->assertEquals(
                '`left` INNER JOIN `right`',
                $obj->get()
            );
        }

        public function testGet_bothTableWithCondition() : void
        {
            $obj = new SQLInnerJoin($this->flavor, 'left', 'right');
            $obj->on('::left.id = ::right.id');
            $this->assertEquals(
                '`left` INNER JOIN `right` ON `left`.`id` = `right`.`id`',
                $obj->get()
            );
        }

        public function testGet_bothTableAlias() : void
        {
            $obj = new SQLInnerJoin(
                $this->flavor,
                ['l' => 'left'],
                ['r' => 'right']);
            $this->assertEquals(
                '`left` AS `l` INNER JOIN `right` AS `r`',
                $obj->get()
            );
        }

        public function testGet_leftQueryNoCondition() : void
        {
            $select = new SQLSelect($this->flavor);
            $select->all()->from('left');
            $obj = new SQLInnerJoin(
                $this->flavor,
                ['l' => $select],
                ['r' => 'right']);
            $this->assertEquals(
                '(SELECT * FROM `left`) AS `l` INNER JOIN `right` AS `r`',
                $obj->get()
            );
        }

        public function testGet_rightQueryNoCondition() : void
        {
            $select = new SQLSelect($this->flavor);
            $select->all()->from('right');
            $obj = new SQLInnerJoin(
                $this->flavor,
                ['l' => 'left'],
                ['r' => $select]);
            $this->assertEquals(
                '`left` AS `l` INNER JOIN (SELECT * FROM `right`) AS `r`',
                $obj->get()
            );
        }

        public function testGet_bothQueryNoCondition() : void
        {
            $left = new SQLSelect($this->flavor);
            $left->all()->from('left');

            $right = new SQLSelect($this->flavor);
            $right->all()->from('right');

            $obj = new SQLInnerJoin(
                $this->flavor,
                ['l' => $left],
                ['r' => $right]);
            $this->assertEquals(
                '(SELECT * FROM `left`) AS `l` INNER JOIN (SELECT * FROM `right`) AS `r`',
                $obj->get()
            );
        }

        public function testGet_bothQueryWithCondition() : void
        {
            $left = new SQLSelect($this->flavor);
            $left->all()->from('left');

            $right = new SQLSelect($this->flavor);
            $right->all()->from('right');

            $obj = new SQLInnerJoin(
                $this->flavor,
                ['l' => $left],
                ['r' => $right]);
                $obj->on('::l.id = ::r.id');
            $this->assertEquals(
                '(SELECT * FROM `left`) AS `l` INNER JOIN (SELECT * FROM `right`) AS `r` ON `l`.`id` = `r`.`id`',
                $obj->get()
            );
        }
    }
?>
p
