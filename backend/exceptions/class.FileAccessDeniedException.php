<?php declare(strict_types=1);

    namespace Shockie\Exceptions;

    use Throwable;
    use Shockie\Exceptions\IOException;

    class FileAccessDeniedException extends IOException
    {
        consT READ = 'read';
        const WRITE = 'write';

        private $path;
        private $mod;

        public function __construct(string $mode, string $path, ?Throwable $previous = null)
        {
            $message = sprintf('Unable to access file "%s" for %s operation', $path, $mode);
            parent::__construct($message, $previous);

            $this->path = $path;
            $this->mode = $mode;
        }

        public function getFilePath() : string
        {
            return $this->path;
        }

        public function getMode() : string
        {
            return $this->mode;
        }
    }
?>
