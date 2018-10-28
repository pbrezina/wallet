<?php declare(strict_types=1);

    namespace Shockie\Exceptions;

    use Throwable;
    use Shockie\Exceptions\InvalidFormatException;

    class InvalidPropertyValueException extends InvalidFormatException
    {
        private $property;
        private $value;
        private $expected;
        private $condition;

        public function __construct(string $property,
                                    $value,
                                    $expected,
                                    string $condition,
                                    ?Throwable $previous = null)
        {
            $message = sprintf('Property [%s] has value [%s], expected %s [%s]',
                               $property, $value, $condition, $expected);
            parent::__construct($message, $previous);

            $this->property = $property;
            $this->value = $value;
            $this->expected = $expected;
            $this->condition = $condition;
        }

        public function getProperty() : string
        {
            return $this->property;
        }

        public function getValue() : string
        {
            return $this->value;
        }

        public function getExpected() : string
        {
            return $this->expected;
        }

        public function getCondition() : string
        {
            return $this->condition;
        }

        static public function MatchTo(string $property,
                                       $value,
                                       $expected) : self
        {
            return new self($property, $value, $expected, 'match to');
        }

        static public function MultipleOf(string $property,
                                          float $value,
                                          float $multipleof) : self
        {
            return new self($property, (string)$value, (string)$multipleof, 'multiple of');
        }

        static public function LessThan(string $property,
                                        float $value,
                                        float $min) : self
        {
            return new self($property, (string)$value, (string)$min, 'less than');
        }

        static public function LessOrEqualTo(string $property,
                                             float $value,
                                             float $min) : self
        {
            return new self($property, (string)$value, (string)$min, 'less than or equal to');
        }

        static public function GreaterThan(string $property,
                                           float $value,
                                           float $max) : self
        {
            return new self($property, (string)$value, (string)$max, 'greater than');
        }

        static public function GreaterOrEqualTo(string $property,
                                                float $value,
                                                float $max) : self
        {
            return new self($property, (string)$value, (string)$max, 'greater than or equal to');
        }
    }
?>
