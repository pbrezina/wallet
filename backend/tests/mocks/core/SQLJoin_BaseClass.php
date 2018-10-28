<?php declare(strict_types=1);

    namespace Shockie\Tests\Mocks\Core;

    use Shockie\Core\SQLJoin;
    use Shockie\Interfaces\ISQLFlavor;

    final class SQLJoin_BaseClass extends SQLJoin
    {
        public function __construct(ISQLFlavor $flavor,
                                    $left,
                                    string $operator,
                                    $right)
        {
            parent::__construct($flavor, $left, $operator, $right);
        }
    }
?>
