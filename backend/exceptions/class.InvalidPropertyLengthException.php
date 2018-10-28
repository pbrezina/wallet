<?php declare(strict_types=1);

    namespace Shockie\Exceptions;

    use Throwable;
    use Shockie\Exceptions\InvalidFormatException;

    class InvalidPropertyLengthException extends InvalidFormatException
    {
        private $property;
        private $length;
        private $min;
        private $max;
        private $condition;

        public function __construct(string $property,
                                    int $length,
                                    ?int $min,
                                    ?int $max,
                                    bool $exclusiveMinimum = false,
                                    bool $exclusiveMaximum = false,
                                    ?Throwable $previous = null)
        {
            $min = $min ?? '-';
            $max = $max ?? '-';

            $message = sprintf('Property [%s] has length [%d], expected range is %s%s,%s%s',
                               $property, $length,
                               ($exclusiveMinimum ? '(' : '<'),
                               $min,
                               $max,
                               ($exclusiveMaximum ? ')' : '>'));
            parent::__construct($message, $previous);

            $this->property = $property;
            $this->length = $length;
            $this->min = $min;
            $this->max = $max;
        }

        public function getProperty() : string
        {
            return $this->property;
        }

        public function getLenght() : int
        {
            return $this->length;
        }

        public function getMin() : int
        {
            return $this->length;
        }

        public function getMax() : int
        {
            return $this->length;
        }
    }
?>
