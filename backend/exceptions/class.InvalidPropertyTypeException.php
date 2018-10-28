<?php declare(strict_types=1);

    namespace Shockie\Exceptions;

    use Throwable;
    use Shockie\Exceptions\InvalidFormatException;

    class InvalidPropertyTypeException extends InvalidFormatException
    {
        private $property;
        private $type;
        private $expected;

        public function __construct(string $property,
                                    string $type,
                                    string $expected,
                                    ?Throwable $previous = null)
        {
            $message = sprintf('Property [%s] has type [%s], expected [%s]',
                               $property, $type, $expected);
            parent::__construct($message, $previous);

            $this->property = $property;
            $this->type = $type;
            $this->expected = $expected;
        }

        public function getProperty() : string
        {
            return $this->property;
        }

        public function getType() : string
        {
            return $this->type;
        }

        public function getExpected() : string
        {
            return $this->expected;
        }
    }
?>
