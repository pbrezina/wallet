<?php declare(strict_types=1);

    namespace Shockie\Tests\Core;

    use PHPUnit\Framework\TestCase;
    use Shockie\Core\SQLResult;
    use Shockie\Tests\Mocks\Core\SQLFlavorMock;
    use Shockie\Tests\Mocks\Core\SQLQuery_BaseClass;

    final class SQLQueryTest extends TestCase
    {
        private $query;

        protected function setUp()
        {
            $this->query = new SQLQuery_BaseClass(
                new SQLFlavorMock(),
                'SELECT * FROM table'
            );
        }

        protected function tearDown()
        {
            unset($this->query);
        }

        public function testGet() : void
        {
            $this->assertEquals(
                'SELECT * FROM table',
                $this->query->get()
            );
        }

        public function testRun() : void
        {
            $this->assertInstanceOf(
                SQLResult::class,
                $this->query->run()
            );
        }
    }
?>
