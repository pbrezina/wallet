<?php declare(strict_types=1);

    namespace Shockie\Tests\Core;

    use PHPUnit\Framework\TestCase;
    use Shockie\Core\JSON;

    final class JSONTest extends TestCase
    {
        public function testParse_empty() : void
        {
            $data = JSON::Parse('');
            $this->assertCount(0, (array)$data);
        }

        public function testParse_emptyObject() : void
        {
            $data = JSON::Parse('{}');
            $this->assertCount(0, (array)$data);
        }

        public function testParse_nonEmpty() : void
        {
            $data = JSON::Parse('{"test":1}');
            $this->assertObjectHasAttribute('test', $data);
            $this->assertEquals(1, $data->test);
        }
    }
?>
