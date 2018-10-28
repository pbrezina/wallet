<?php declare(strict_types=1);

    namespace Shockie\Tests\Core;

    use in_array;
    use InvalidArgumentException;
    use PHPUnit\Framework\TestCase;
    use Shockie\Core\JSON;
    use Shockie\Core\JSONSchema;
    use Shockie\Exceptions\InvalidFormatException;
    use Shockie\Exceptions\InvalidPropertyLengthException;
    use Shockie\Exceptions\InvalidPropertyTypeException;
    use Shockie\Exceptions\InvalidPropertyValueException;
    use Shockie\Exceptions\MissingPropertyException;

    final class JSONSchemaTest extends TestCase
    {
        public function testLoad_invalid_schema() : void
        {
            $this->expectException(InvalidFormatException::class);
            $schema = new JSONSchema('invalid schema', JSONSchema::STRING);
            $schema->load();
        }

        public function testLoad_invalid_type() : void
        {
            $this->expectException(InvalidArgumentException::class);
            $schema = new JSONSchema('{}', 0);
            $schema->load();
        }

        public function testValidate_empty() : void
        {
            $schema = new JSONSchema('{}', JSONSchema::STRING);
            $data = JSON::Parse('{}');

            $schema->validate($data);
            $this->addToAssertionCount(1);
        }

        public function testValidate_emptyWithData() : void
        {
            $schema = new JSONSchema('{}', JSONSchema::STRING);
            $data = JSON::Parse('{"test": 1}');

            $schema->validate($data);
            $this->addToAssertionCount(1);
        }

        /**
         * @dataProvider mixedTypesProvider
         */
        public function testValidate_object($value) : void
        {
            $schema = '{
                "type": "object"
            }';

            $schema = new JSONSchema($schema, JSONSchema::STRING);
            $data = JSON::Parse($value);

            if (is_object($data)) {
                $this->addToAssertionCount(1);
            } else {
                $this->expectException(InvalidPropertyTypeException::class);
            }

            $schema->validate($data);
        }


        /**
         * @dataProvider objectsProvider
         */
        public function testValidate_object_properties($value) : void
        {
            $schema = '{
                "type": "object",
                "properties": {
                    "name": {"type": "string"},
                    "id": {"type": "integer"}
                }
            }';

            $schema = new JSONSchema($schema, JSONSchema::STRING);
            $data = JSON::Parse($value);

            if (isset($data->name) && !is_string($data->name)) {
                $this->expectException(InvalidPropertyTypeException::class);
            } else if (isset($data->id) && !is_integer($data->id)) {
                $this->expectException(InvalidPropertyTypeException::class);
            } else {
                $this->addToAssertionCount(1);
            }

            $schema->validate($data);
        }

        /**
         * @dataProvider objectsProvider
         */
        public function testValidate_object_required($value) : void
        {
            $schema = '{
                "type": "object",
                "properties": {
                },
                "required": ["name"]
            }';

            $schema = new JSONSchema($schema, JSONSchema::STRING);
            $data = JSON::Parse($value);

            if (!isset($data->name)) {
                $this->expectException(MissingPropertyException::class);
            } else {
                $this->addToAssertionCount(1);
            }

            $schema->validate($data);
        }

        /**
         * @dataProvider mixedTypesProvider
         */
        public function testValidate_array($value) : void
        {
            $schema = '{
                "type": "array"
            }';

            $schema = new JSONSchema($schema, JSONSchema::STRING);
            $data = JSON::Parse($value);

            if (is_array($data)) {
                $this->addToAssertionCount(1);
            } else {
                $this->expectException(InvalidPropertyTypeException::class);
            }

            $schema->validate($data);
        }

        /**
         * @dataProvider arraysProvider
         */
        public function testValidate_array_minItems($value) : void
        {
            $schema = '{
                "type": "array",
                "minItems": 3
            }';

            $schema = new JSONSchema($schema, JSONSchema::STRING);
            $data = JSON::Parse($value);

            if (count($data) < 3) {
                $this->expectException(InvalidPropertyLengthException::class);
            } else {
                $this->addToAssertionCount(1);
            }
            $schema->validate($data);
        }

        /**
         * @dataProvider arraysProvider
         */
        public function testValidate_array_maxItems($value) : void
        {
            $schema = '{
                "type": "array",
                "maxItems": 3
            }';

            $schema = new JSONSchema($schema, JSONSchema::STRING);
            $data = JSON::Parse($value);

            if (count($data) > 3) {
                $this->expectException(InvalidPropertyLengthException::class);
            } else {
                $this->addToAssertionCount(1);
            }
            $schema->validate($data);
        }

        /**
         * @dataProvider arraysProvider
         */
        public function testValidate_array_items($value) : void
        {
            $schema = '{
                "type": "array",
                "items": {
                    "type": "integer"
                }
            }';

            $schema = new JSONSchema($schema, JSONSchema::STRING);
            $data = JSON::Parse($value);
            $all_ints = true;

            foreach ($data as $value) {
                if (!is_integer($value)) {
                    $all_ints = false;
                    break;
                }
            }

            if (!$all_ints) {
                $this->expectException(InvalidPropertyTypeException::class);
            } else {
                $this->addToAssertionCount(1);
            }
            $schema->validate($data);
        }

        /**
         * @dataProvider mixedTypesProvider
         */
        public function testValidate_string($value) : void
        {
            $schema = '{
                "type": "string"
            }';

            $schema = new JSONSchema($schema, JSONSchema::STRING);
            $data = JSON::Parse($value);

            if (is_string($data)) {
                $this->addToAssertionCount(1);
            } else {
                $this->expectException(InvalidPropertyTypeException::class);
            }

            $schema->validate($data);
        }

        /**
         * @dataProvider stringsProvider
         */
        public function testValidate_string_minLength($value) : void
        {
            $schema = '{
                "type": "string",
                "minLength": 7
            }';

            $schema = new JSONSchema($schema, JSONSchema::STRING);
            $data = JSON::Parse($value);

            if (strlen($data) < 7) {
                $this->expectException(InvalidPropertyLengthException::class);
            } else {
                $this->addToAssertionCount(1);
            }
            $schema->validate($data);
        }

        /**
         * @dataProvider stringsProvider
         */
        public function testValidate_string_maxLength($value) : void
        {
            $schema = '{
                "type": "string",
                "maxLength": 7
            }';

            $schema = new JSONSchema($schema, JSONSchema::STRING);
            $data = JSON::Parse($value);

            if (strlen($data) > 7) {
                $this->expectException(InvalidPropertyLengthException::class);
            } else {
                $this->addToAssertionCount(1);
            }
            $schema->validate($data);
        }

         /**
         * @dataProvider stringsProvider
         */
        public function testValidate_string_pattern($value) : void
        {
            $schema = '{
                "type": "string",
                "pattern": ".+(sp|/).+"
            }';

            $schema = new JSONSchema($schema, JSONSchema::STRING);
            $data = JSON::Parse($value);

            if (!preg_match('/.+(sp|\\/).+/', $data)) {
                $this->expectException(InvalidPropertyValueException::class);
            } else {
                $this->addToAssertionCount(1);
            }
            $schema->validate($data);
        }

        /**
         * @dataProvider mixedTypesProvider
         */
        public function testValidate_integer($value) : void
        {
            $schema = '{
                "type": "integer"
            }';

            $schema = new JSONSchema($schema, JSONSchema::STRING);
            $data = JSON::Parse($value);

            if (is_integer($data)) {
                $this->addToAssertionCount(1);
            } else {
                $this->expectException(InvalidPropertyTypeException::class);
            }

            $schema->validate($data);
        }

        /**
         * @dataProvider integersProvider
         */
        public function testValidate_integer_minimum($threshold, $value) : void
        {
            $schema = '{
                "type": "integer",
                "minimum": ' . $threshold . '
            }';

            $schema = new JSONSchema($schema, JSONSchema::STRING);
            $data = JSON::Parse($value);

            if ($data < $threshold) {
                $this->expectException(InvalidPropertyValueException::class);
            } else {
                $this->addToAssertionCount(1);
            }

            $schema->validate($data);
        }

        /**
         * @dataProvider integersProvider
         */
        public function testValidate_integer_exclusiveminimum($threshold, $value) : void
        {
            $schema = '{
                "type": "integer",
                "exclusiveMinimum": ' . $threshold . '
            }';

            $schema = new JSONSchema($schema, JSONSchema::STRING);
            $data = JSON::Parse($value);

            if ($data <= $threshold) {
                $this->expectException(InvalidPropertyValueException::class);
            } else {
                $this->addToAssertionCount(1);
            }

            $schema->validate($data);
        }

        /**
         * @dataProvider integersProvider
         */
        public function testValidate_integer_maximum($threshold, $value) : void
        {
            $schema = '{
                "type": "integer",
                "maximum": ' . $threshold . '
            }';

            $schema = new JSONSchema($schema, JSONSchema::STRING);
            $data = JSON::Parse($value);

            if ($data > $threshold) {
                $this->expectException(InvalidPropertyValueException::class);
            } else {
                $this->addToAssertionCount(1);
            }

            $schema->validate($data);
        }

        /**
         * @dataProvider integersProvider
         */
        public function testValidate_integer_exclusivemaximum($threshold, $value) : void
        {
            $schema = '{
                "type": "integer",
                "exclusiveMaximum": ' . $threshold . '
            }';

            $schema = new JSONSchema($schema, JSONSchema::STRING);
            $data = JSON::Parse($value);

            if ($data >= $threshold) {
                $this->expectException(InvalidPropertyValueException::class);
            } else {
                $this->addToAssertionCount(1);
            }

            $schema->validate($data);
        }

        /**
         * @dataProvider integersProvider
         */
        public function testValidate_integer_multipleOf($threshold, $value) : void
        {
            $schema = '{
                "type": "integer",
                "multipleOf": ' . $threshold . '
            }';

            $schema = new JSONSchema($schema, JSONSchema::STRING);
            $data = JSON::Parse($value);

            if ($data % $threshold != 0) {
                $this->expectException(InvalidPropertyValueException::class);
            } else {
                $this->addToAssertionCount(1);
            }

            $schema->validate($data);
        }

        /**
         * @dataProvider mixedTypesProvider
         */
        public function testValidate_number($value) : void
        {
            $schema = '{
                "type": "number"
            }';

            $schema = new JSONSchema($schema, JSONSchema::STRING);
            $data = JSON::Parse($value);

            if (is_integer($data) || is_float($data)) {
                $this->addToAssertionCount(1);
            } else {
                $this->expectException(InvalidPropertyTypeException::class);
            }

            $schema->validate($data);
        }

        /**
         * @dataProvider numbersProvider
         */
        public function testValidate_number_minimum($threshold, $value) : void
        {
            $schema = '{
                "type": "number",
                "minimum": ' . $threshold . '
            }';

            $schema = new JSONSchema($schema, JSONSchema::STRING);
            $data = JSON::Parse($value);

            if ($data < $threshold) {
                $this->expectException(InvalidPropertyValueException::class);
            } else {
                $this->addToAssertionCount(1);
            }

            $schema->validate($data);
        }

        /**
         * @dataProvider numbersProvider
         */
        public function testValidate_number_exclusiveminimum($threshold, $value) : void
        {
            $schema = '{
                "type": "number",
                "exclusiveMinimum": ' . $threshold . '
            }';

            $schema = new JSONSchema($schema, JSONSchema::STRING);
            $data = JSON::Parse($value);

            if ($data <= $threshold) {
                $this->expectException(InvalidPropertyValueException::class);
            } else {
                $this->addToAssertionCount(1);
            }

            $schema->validate($data);
        }

        /**
         * @dataProvider numbersProvider
         */
        public function testValidate_number_maximum($threshold, $value) : void
        {
            $schema = '{
                "type": "number",
                "maximum": ' . $threshold . '
            }';

            $schema = new JSONSchema($schema, JSONSchema::STRING);
            $data = JSON::Parse($value);

            if ($data > $threshold) {
                $this->expectException(InvalidPropertyValueException::class);
            } else {
                $this->addToAssertionCount(1);
            }

            $schema->validate($data);
        }

        /**
         * @dataProvider numbersProvider
         */
        public function testValidate_number_exclusivemaximum($threshold, $value) : void
        {
            $schema = '{
                "type": "number",
                "exclusiveMaximum": ' . $threshold . '
            }';

            $schema = new JSONSchema($schema, JSONSchema::STRING);
            $data = JSON::Parse($value);

            if ($data >= $threshold) {
                $this->expectException(InvalidPropertyValueException::class);
            } else {
                $this->addToAssertionCount(1);
            }

            $schema->validate($data);
        }

        /**
         * @dataProvider numbersProvider
         */
        public function testValidate_number_multipleOf($threshold, $value) : void
        {
            $schema = '{
                "type": "number",
                "multipleOf": ' . $threshold . '
            }';

            $schema = new JSONSchema($schema, JSONSchema::STRING);
            $data = JSON::Parse($value);

            if ($data % $threshold != 0) {
                $this->expectException(InvalidPropertyValueException::class);
            } else {
                $this->addToAssertionCount(1);
            }

            $schema->validate($data);
        }

        /**
         * @dataProvider mixedTypesProvider
         */
        public function testValidate_boolean($value) : void
        {
            $schema = '{
                "type": "boolean"
            }';

            $schema = new JSONSchema($schema, JSONSchema::STRING);
            $data = JSON::Parse($value);

            if (is_bool($data)) {
                $this->addToAssertionCount(1);
            } else {
                $this->expectException(InvalidPropertyTypeException::class);
            }

            $schema->validate($data);
        }

        /**
         * @dataProvider mixedTypesProvider
         */
        public function testValidate_null($value) : void
        {
            $schema = '{
                "type": "null"
            }';

            $schema = new JSONSchema($schema, JSONSchema::STRING);
            $data = JSON::Parse($value);

            if (is_null($data)) {
                $this->addToAssertionCount(1);
            } else {
                $this->expectException(InvalidPropertyTypeException::class);
            }

            $schema->validate($data);
        }

        /**
         * @dataProvider enumsProvider
         */
        public function testValidate_enum($value) : void
        {
            $schema = '{
                "type": "integer",
                "enum": [1, 2, 3]
            }';

            $schema = new JSONSchema($schema, JSONSchema::STRING);
            $data = JSON::Parse($value);

            if (in_array($data, [1, 2, 3])) {
                $this->addToAssertionCount(1);
            } else {
                $this->expectException(InvalidPropertyValueException::class);
            }

            $schema->validate($data);
        }

        /**
         * @dataProvider enumsProvider
         */
        public function testValidate_const($value) : void
        {
            $schema = '{
                "type": "integer",
                "const": 1
            }';

            $schema = new JSONSchema($schema, JSONSchema::STRING);
            $data = JSON::Parse($value);

            if ($data === 1) {
                $this->addToAssertionCount(1);
            } else {
                $this->expectException(InvalidPropertyValueException::class);
            }

            $schema->validate($data);
        }

        /**
         * @dataProvider complexProvider
         */
        public function testValidate_complex($value, $exception) : void
        {
            $schema = '{
                "type": "object",
                "properties": {
                    "name": {"type": "string"},
                    "id": {"type": "number"},
                    "address" : {
                        "type": "array",
                        "items": {
                            "type": "object",
                            "properties" : {
                                "street" : {"type": "string"}
                            },
                            "required" : ["street"]
                        }
                    }
                },
                "required": ["name", "id"]
            }';

            $schema = new JSONSchema($schema, JSONSchema::STRING);
            $data = JSON::Parse($value);

            if ($exception !== null) {
                $this->expectException($exception);
            } else {
                $this->addToAssertionCount(1);
            }

            $schema->validate($data);
        }

        public function mixedTypesProvider() : array
        {
            return [
                ['"string"'],
                ['true'], ['false'],
                ['0'], ['1'], ['100'],
                ['0.1'], ['10.0'],
                ['null'],
                ['[0, 1, 2]'],
                ['{"test":"value"}']
            ];
        }

        public function objectsProvider() : array
        {
            return [
                ['{"name": "John", "id": 1}'],
                ['{"name": "John", "id": "test"}'],
                ['{"name": 1, "id": 1}'],
                ['{"name": 1, "id": "test"}'],
                ['{"name": "John"}'],
                ['{"id": 1}'],
                ['{}']
            ];
        }

        public function arraysProvider() : array
        {
            return [
                ['[1, 2, 3, 4, 5, 6, 7, 8, 9, 10]'],
                ['[1, 2, 3]'],
                ['[1, 2]'],
                ['["a", "b", "c", "d", "e"]'],
                ['[false, false, false, true, true]'],
                ['[null, "a", 1, true]']
            ];
        }

        public function stringsProvider() : array
        {
            return [
                ['"jury"'],
                ['"banquet"'],
                ['"expect"'],
                ['"hot"'],
                ['"spokesperson"'],
                ['"spokesp/erson"'],
                ['"bat"'],
                ['"copy"'],
                ['"atmosphere"'],
                ['"grind"'],
                ['"advocate"']
            ];
        }

        public function integersProvider() : array
        {
            $array = [];

            for ($i = 1; $i <= 30; $i++) {
                $array[] = [8, (string)$i];
            }

            return $array;
        }

        public function numbersProvider() : array
        {
            $array = [];

            for ($i = 1; $i <= 15; $i++) {
                $array[] = [8, (string)($i + 0.5)];
            }

            for ($i = 15; $i <= 30; $i++) {
                $array[] = [8, (string)$i];
            }

            return $array;
        }

        public function complexProvider() : array
        {
            return [
                ['{}', MissingPropertyException::class],
                ['{"name": "test"}', MissingPropertyException::class],
                ['{"name": 1, "id": "test"}', InvalidPropertyTypeException::class],
                ['{"name": "John", "id": 1}', null],
                ['{"name": "John", "id": 1, "address": 1}', InvalidPropertyTypeException::class],
                ['{"name": "John", "id": 1, "address": []}', null],
                ['{"name": "John", "id": 1, "address": [{}]}', MissingPropertyException::class],
                ['{"name": "John", "id": 1, "address": [{"id": 2}]}', MissingPropertyException::class],
                ['{"name": "John", "id": 1, "address": [{"street": 2}]}', InvalidPropertyTypeException::class],
                ['{"name": "John", "id": 1, "address": [{"street": "test"}]}', null],
                ['{"name": "John", "id": 1, "address": [{"street": "test"}, {}]}', MissingPropertyException::class],
                ['{"name": "John", "id": 1, "address": [{"street": "test"}, {"street": 1}]}', InvalidPropertyTypeException::class],
                ['{"name": "John", "id": 1, "address": [{"street": "test"}, {"street": "another"}]}', null]
            ];
        }

        public function enumsProvider() : array
        {
            $array = [];

            for ($i = 1; $i <= 30; $i++) {
                $array[] = [(string)$i];
            }

            return $array;
        }
    }
?>
