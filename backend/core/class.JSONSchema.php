<?php declare(strict_types=1);

    namespace Shockie\Core;

    use in_array;
    use gettype;
    use Closure;
    use InvalidArgumentException;
    use Shockie\Core\JSON;
    use Shockie\Exceptions\InvalidFormatException;
    use Shockie\Exceptions\InvalidPropertyLengthException;
    use Shockie\Exceptions\InvalidPropertyTypeException;
    use Shockie\Exceptions\InvalidPropertyValueException;
    use Shockie\Exceptions\MissingPropertyException;
    use Shockie\Interfaces\IValidator;

    /**
     * JSON Schema Validator.
     *
     * Validate JSON data against given schema.
     */
    class JSONSchema implements IValidator
    {
        const FILE = 1;
        const URI = 2;
        const STRING = 3;

        private $loaded;
        private $source;
        private $origin;
        private $schema;

        /**
         * Construct JSON Schema from various sources.
         *
         * The $source parameter may be one of:
         *   self::FILE - $schema is a path to a file.
         *   self::URI - $schema is a generic URI.
         *   self::STRING - $schema is stored as string.
         *
         * The schema is lazy-loaded when self::validate() is called.
         *
         * The schema validation currently supports these specifications:
         * - enum
         * - const
         * - type: object
         *   - properties
         *   - required
         * - type: array
         *   - minItems
         *   - maxItems
         *   - items (List validation)
         * - type: string:
         *   - minLength
         *   - maxLength
         *   - pattern
         * - type: integer:
         *   - minimum
         *   - exclusiveMinimum
         *   - maximum
         *   - exclusiveMaximum
         *   - multipleOf
         * - type: number:
         *   - minimum
         *   - exclusiveMinimum
         *   - maximum
         *   - exclusiveMaximum
         *   - multipleOf
         * - type: boolean
         * - type: null
         *
         * @param string $schema
         * @param integer $source
         */
        public function __construct(string $schema, int $source = self::FILE)
        {
            $this->loaded = false;
            $this->source = $source;
            $this->origin = $schema;
            $this->schema = null;
        }

        /**
         * Load JSON schema.
         *
         * @return void
         */
        public function load() : void
        {
            if ($this->loaded) {
                return;
            }

            switch ($this->source) {
            case self::FILE:
                $this->schema = JSON::ParseFromFile($this->origin);
                break;
            case self::URI:
                $this->schema = JSON::ParseFromURI($this->origin);
                break;
            case self::STRING:
                $this->schema = JSON::Parse($this->origin);
                break;
            default:
                throw new InvalidArgumentException('Unknown schema source: ' . $this->source);
            }
        }

        /**
         * Validates $data against the JSON schema.
         *
         * @param mixed $data
         * @return void
         */
        public function validate($data) : void
        {
            $this->load();

            $this->validate_step('schema:', '', $this->schema, $data);

            return;
        }

        private function validator($callback)
        {
            return Closure::fromCallable([$this, $callback]);
        }

        private function validate_step(string $schema_path,
                                       string $data_path,
                                       $schema,
                                       $data) : void
        {
            if (is_object($schema)) {
                $this->validate_type($schema_path, $data_path, $schema, $data);
                return;
            }

            if (is_bool($schema)) {
                if ($schema) {
                    return;
                } else {
                    throw new InvalidFormatException(
                        sprintf('Property [%s] failed validation against [%s (false)]',
                                $data_path, $schema_path)
                    );
                }
            }

            throw new InvalidFormatException(
                sprintf('[%s] is invalid schema', $schema_path)
            );
        }

        private function validate_type(string $schema_path,
                                       string $data_path,
                                       $schema,
                                       $data) : void
        {
            if (!isset($schema->type)) {
                return;
            }

            $types = $schema->type;
            if (!is_array($schema->type)) {
                $types = [$schema->type];
            }

            foreach ($types as $type) {
                $match = false;
                switch ($schema->type) {
                case 'object':
                    $match = is_object($data);
                    $validator = $this->validator('validate_object');
                    break;
                case 'array':
                    $match = is_array($data);
                    $validator = $this->validator('validate_array');
                    break;
                case 'string':
                    $match = is_string($data);
                    $validator = $this->validator('validate_string');
                    break;
                case 'integer':
                    $match = is_integer($data);
                    $validator = $this->validator('validate_number');
                    break;
                case 'number':
                    $match = is_integer($data) || is_float($data);
                    $validator = $this->validator('validate_number');
                    break;
                case 'boolean':
                    $match = is_bool($data);
                    $validator = null;
                    break;
                case 'null':
                    $match = is_null($data);
                    $validator = null;
                    break;
                default:
                    throw new InvalidFormatException(
                        sprintf('[%s/type]: unknown type [%s]',
                                $schema_path, $type)
                    );
                    break;
                }

                if ($match) {
                    break;
                }
            }

            if (!$match) {
                throw new InvalidPropertyTypeException(
                    $data_path, gettype($data), $type
                );
            }

            if ($validator === null) {
                return;
            }

            $this->validate_generic($schema_path, $data_path, $schema, $data);
            call_user_func($validator, $schema_path, $data_path, $schema, $data);
        }

        /**
         * Supported schema specification:
         * - enum
         * - const
         */
        private function validate_generic(string $schema_path,
                                          string $data_path,
                                          object $schema,
                                          $data) : void
        {
            $enum = isset($schema->enum) ? $schema->enum : null;

            if (isset($schema->const)) {
                $enum = [$schema->const];
            }

            if ($enum === null) {
                return;
            }

            if (!in_array($data, $enum, true)) {
                throw InvalidPropertyValueException::MatchTo(
                    $data_path, $data, implode("|", $enum)
                );
            }
        }

        /**
         * Supported schema specification:
         * - properties
         * - required
         */
        private function validate_object(string $schema_path,
                                         string $data_path,
                                         object $schema,
                                         $data) : void
        {
            $required = $schema->required ?? [];
            $properties = (array)($schema->properties ?? []);

            foreach ($required as $property) {
                if (!isset($data->{$property}) || $data->{$property} === null) {
                    throw new MissingPropertyException(
                        $data_path . '/' . $property
                    );
                }
            }

            foreach ($properties as $property => $schema) {
                if (!isset($data->{$property})) {
                    continue;
                }

                $this->validate_step($schema_path . '/properties/' . $property,
                                     $data_path . '/' . $property,
                                     $schema, $data->{$property});
            }
        }

        /**
         * Supported schema specification:
         * - minItems
         * - maxItems
         * - items (List validation)
         */
        private function validate_array(string $schema_path,
                                        string $data_path,
                                        object $schema,
                                        $data) : void
        {
            $min = $schema->minItems ?? null;
            $max = $schema->maxItems ?? null;

            if ($min !== null) {
                $length = count($data);
                if ($length < $min) {
                    throw new InvalidPropertyLengthException(
                        $data_path, $length, $min, $max
                    );
                }
            }

            if ($max !== null) {
                $length = count($data);
                if ($length > $max) {
                    throw new InvalidPropertyLengthException(
                        $data_path, $length, $min, $max
                    );
                }
            }

            if (isset($schema->items)) {
                foreach ($data as $index => $item) {
                    $this->validate_step($schema_path . '/items',
                                         $data_path . '/[' . $index . ']',
                                         $schema->items, $item);
                }
            }
        }

        /**
         * Supported schema specification:
         * - minLength
         * - maxLength
         * - pattern
         */
        private function validate_string(string $schema_path,
                                         string $data_path,
                                         object $schema,
                                         $data) : void
        {
            $min = $schema->minLength ?? null;
            $max = $schema->maxLength ?? null;

            if ($min !== null) {
                $length = strlen($data);
                if ($length < $min) {
                    throw new InvalidPropertyLengthException(
                        $data_path, $length, $min, $max
                    );
                }
            }

            if ($max !== null) {
                $length = strlen($data);
                if ($length > $max) {
                    throw new InvalidPropertyLengthException(
                        $data_path, $length, $min, $max
                    );
                }
            }

            if (isset($schema->pattern)) {
                $pattern = str_replace('/', '\\/', $schema->pattern);
                if (!preg_match('/' . $pattern . '/', $data)) {
                    throw InvalidPropertyValueException::MatchTo(
                        $data_path, $data, $schema->pattern
                    );
                }
            }
        }

        /**
         * Supported schema specification:
         * - minimum
         * - exclusiveMinimum
         * - maximum
         * - exclusiveMaximum
         * - multipleOf
         */
        private function validate_number(string $schema_path,
                                         string $data_path,
                                         object $schema,
                                         $data) : void
        {
            if (isset($schema->exclusiveMinimum)) {
                if ($data <= $schema->exclusiveMinimum) {
                    throw InvalidPropertyValueException::LessThan(
                        $data_path, $data, $schema->exclusiveMinimum
                    );
                }
            }

            if (isset($schema->minimum)) {
                if ($data < $schema->minimum) {
                    throw InvalidPropertyValueException::LessOrEqualTo(
                        $data_path, $data, $schema->minimum
                    );
                }
            }

            if (isset($schema->exclusiveMaximum)) {
                if ($data >= $schema->exclusiveMaximum) {
                    throw InvalidPropertyValueException::GreaterThan(
                        $data_path, $data, $schema->exclusiveMaximum
                    );
                }
            }

            if (isset($schema->maximum)) {
                if ($data > $schema->maximum) {
                    throw InvalidPropertyValueException::GreaterOrEqualTo(
                        $data_path, $data, $schema->maximum
                    );
                }
            }

            if (isset($schema->multipleOf)) {
                if ($data % $schema->multipleOf != 0) {
                    throw InvalidPropertyValueException::MultipleOf(
                        $data_path, $data, $schema->multipleOf
                    );
                }
            }
        }
    }
?>
