<?php declare(strict_types=1);

    namespace Shockie\Tests\Core;

    use PHPUnit\Framework\TestCase;
    use Shockie\Core\SQLInnerJoin;
    use Shockie\Core\SQLSelect;
    use Shockie\Tests\Mocks\Core\SQLFlavorMock;
    use Shockie\Tests\Mocks\Core\SQLJoin_BaseClass;

    final class SQLJoinTest extends TestCase
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
            $obj = new SQLJoin_BaseClass($this->flavor, 'left', 'JOIN', 'right');
            $this->assertEquals(
                '`left` JOIN `right`',
                $obj->get()
            );
        }

        public function testGet_bothTableWithCondition() : void
        {
            $obj = new SQLJoin_BaseClass($this->flavor, 'left', 'JOIN', 'right');
            $obj->on('::left.id = ::right.id');
            $this->assertEquals(
                '`left` JOIN `right` ON `left`.`id` = `right`.`id`',
                $obj->get()
            );
        }

        public function testGet_bothTableAlias() : void
        {
            $obj = new SQLJoin_BaseClass(
                $this->flavor,
                ['l' => 'left'],
                'JOIN',
                ['r' => 'right']);
            $this->assertEquals(
                '`left` AS `l` JOIN `right` AS `r`',
                $obj->get()
            );
        }

        public function testGet_leftQueryNoCondition() : void
        {
            $select = new SQLSelect($this->flavor);
            $select->all()->from('left');
            $obj = new SQLJoin_BaseClass(
                $this->flavor,
                ['l' => $select],
                'JOIN',
                ['r' => 'right']);
            $this->assertEquals(
                '(SELECT * FROM `left`) AS `l` JOIN `right` AS `r`',
                $obj->get()
            );
        }

        public function testGet_rightQueryNoCondition() : void
        {
            $select = new SQLSelect($this->flavor);
            $select->all()->from('right');
            $obj = new SQLJoin_BaseClass(
                $this->flavor,
                ['l' => 'left'],
                'JOIN',
                ['r' => $select]);
            $this->assertEquals(
                '`left` AS `l` JOIN (SELECT * FROM `right`) AS `r`',
                $obj->get()
            );
        }

        public function testGet_bothQueryNoCondition() : void
        {
            $left = new SQLSelect($this->flavor);
            $left->all()->from('left');

            $right = new SQLSelect($this->flavor);
            $right->all()->from('right');

            $obj = new SQLJoin_BaseClass(
                $this->flavor,
                ['l' => $left],
                'JOIN',
                ['r' => $right]);
            $this->assertEquals(
                '(SELECT * FROM `left`) AS `l` JOIN (SELECT * FROM `right`) AS `r`',
                $obj->get()
            );
        }

        public function testGet_bothQueryWithCondition() : void
        {
            $left = new SQLSelect($this->flavor);
            $left->all()->from('left');

            $right = new SQLSelect($this->flavor);
            $right->all()->from('right');

            $obj = new SQLJoin_BaseClass(
                $this->flavor,
                ['l' => $left],
                'JOIN',
                ['r' => $right]);
                $obj->on('::l.id = ::r.id');
            $this->assertEquals(
                '(SELECT * FROM `left`) AS `l` JOIN (SELECT * FROM `right`) AS `r` ON `l`.`id` = `r`.`id`',
                $obj->get()
            );
        }
    }
?>
p
