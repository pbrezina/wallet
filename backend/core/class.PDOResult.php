<?php declare(strict_types=1);

    namespace Shockie\Core;

    use PDO;
    use PDOStatement;
    use Traversable;
    use Shockie\Core\SQLResult;

    /**
     * SQL Result Base class.
     *
     * Extend this class to create SQL result accessor.
     */
    class PDOResult extends SQLResult
    {
        private $result;

        public function __construct(PDOStatement $result)
        {
            $this->result = $result;
            $this->asObject();
        }

        public function fetch()
        {
            $row = $this->result->fetch();
            if ($row === false) {
                return null;
            }

            return $row;
        }

        public function fetchAll() : array
        {
            return $this->result->fetchAll();
        }

        public function asArray() : parent
        {
            $this->result->setFetchMode(PDO::FETCH_NUM);
            return $this;
        }

        public function asMap() : parent
        {
            $this->result->setFetchMode(PDO::FETCH_ASSOC);
            return $this;
        }

        public function asObject() : parent
        {
            $this->result->setFetchMode(PDO::FETCH_OBJ);
            return $this;
        }

        public function getIterator() : Traversable
        {
            return $this->result;
        }
    }
?>
