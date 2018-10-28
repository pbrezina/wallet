<?php declare(strict_types=1);

    namespace Shockie\Exceptions;

    use Exception;
    use Throwable;

    class ClassNotFoundException extends Exception
    {
        private $class;

        public function __construct(string $class, ?Throwable $previous = null)
        {
            $message = sprintf('Class "%s" was not found', $class);
            parent::__construct($message, 0, $previous);

            $this->class = $class;
        }

        public function getFilePath() : string
        {
            return $this->class;
        }
    }
?>
