<?php declare(strict_types=1);

    namespace Shockie\Tests\Mocks\Core;

    use Shockie\Core\SQLQuery;
    use Shockie\Interfaces\ISQLFlavor;

    final class SQLQuery_BaseClass extends SQLQuery
    {
        private $query;

        public function __construct(ISQLFlavor $flavor,
                                    string $query)
        {
            parent::__construct($flavor);

            $this->query = $query;
        }

        public function get() : string
        {
            return $this->query;
        }
    }
?>
