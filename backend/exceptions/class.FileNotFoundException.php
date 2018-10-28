<?php declare(strict_types=1);

    namespace Shockie\Exceptions;

    use Exception;
    use Throwable;

    class FileNotFoundException extends Exception
    {
        private $path;

        public function __construct(string $path, ?Throwable $previous = null)
        {
            $message = sprintf('File "%s" was not found', $path);
            parent::__construct($message, 0, $previous);

            $this->path = $path;
        }

        public function getFilePath() : string
        {
            return $this->path;
        }
    }
?>
