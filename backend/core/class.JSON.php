<?php declare(strict_types=1);

    namespace Shockie\Core;

    use Shockie\Exceptions\FileAccessDeniedException;
    use Shockie\Exceptions\FileNotFoundException;
    use Shockie\Exceptions\InvalidFormatException;
    use Shockie\Exceptions\IOException;

    /**
     * Helper functions for parsing JSON content.
     */
    class JSON
    {
        /**
         * Parse JSON from string.
         *
         * @param string $json JSON data.
         * @return mixed
         *
         * @throws InvalidFormatException In case of an error.
         */
        static public function Parse(string $json)
        {
            if (empty($json)) {
                return json_decode('{}');
            }

            $parsed = json_decode($json);
            if (json_last_error() != JSON_ERROR_NONE) {
                throw new InvalidFormatException(
                    'Unable to parse input as json data: ' . json_last_error_msg()
                );
            }

            return $parsed;
        }

        /**
         * Parse JSON from file.
         *
         * @param string $path Path to the file.
         * @return mixed
         *
         * @throws FileNotFoundException If the file does not exist.
         * @throws FileAccessDeniedException If the file is not readable.
         * @throws InvalidFormatException In case of an error.
         */
        static public function ParseFromFile(string $path)
        {
            if (!file_exists($path)) {
                throw new FileNotFoundException($path);
            }

            if (!is_readable($path)) {
                throw new FileAccessDeniedException(
                    FileAccessDeniedException::READ,
                    $path
                );
            }

            return self::ParseFromURI($path);
        }

        /**
         * Parse JSON from an URI.
         *
         * @param string $uri URI location.
         * @return mixed
         *
         * @throws IOException If the URI cannot be used..
         * @throws InvalidFormatException In case of an error.
         */
        static public function ParseFromURI(string $uri)
        {
            $json = @file_get_contents($uri);
            if ($json === false) {
                throw new IOException("Unable to read file: " . $uri);
            }

            return self::Parse($json);
        }
    }
?>
