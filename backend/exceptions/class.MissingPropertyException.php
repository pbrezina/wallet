<?php declare(strict_types=1);

    namespace Shockie\Exceptions;

    use Throwable;
    use Shockie\Exceptions\InvalidFormatException;

    class MissingPropertyException extends InvalidFormatException
    {
        private $property;

        public function __construct(string $property,
                                    ?Throwable $previous = null)
        {
            parent::__construct('Missing property: ' . $property, $previous);

            $this->property = $property;
        }

        public function getProperty() : string
        {
            return $this->property;
        }
    }
?>
