<?php declare(strict_types=1);

    namespace Shockie\Core;

    use Shockie\Core\RPCModule;
    use Shockie\Exceptions\ClassNotFoundException;
    use Shockie\Exceptions\FileNotFoundException;
    use Shockie\Exceptions\RPCDisabledModuleException;

    /**
     * Defines an RPC Router Module.
     *
     * This class is for internal use in RPCRouter only.
     */
    class RPCRouterModule
    {
        private $location;
        private $classname;
        private $enabled;
        private $loaded;

        /**
         * Create an RPC Router module definition.
         *
         * @param string $name Module name.
         * @param string $location Path to the file that includes the module.
         * @param string $classname Name of the class that implements the module.
         * @param boolean $enabled True if the module is enabled.
         */
        public function __construct(string $name,
                                    string $location,
                                    string $classname,
                                    bool $enabled)
        {
            $this->name = $name;
            $this->classname = $classname;
            $this->enabled = $enabled;
            $this->loaded = false;

            $this->location = $location[0] == '/'
                ? $location
                : __DIR__ . '/../' . $location;
        }

        /**
         * Load the module.
         *
         * @return void
         *
         * @throws RPCDisabledModuleException If the module is disabled.
         * @throws FileNotFoundException If the file containing the module is not found.
         * @throws ClassNotFoundException If the class implementing the module is not found.
         */
        public function load() : void
        {
            if ($this->loaded) {
                return;
            }

            if (!$this->enabled) {
                throw new RPCDisabledModuleException($this->name);
            }

            if (!file_exists($this->location)) {
                 throw new FileNotFoundException($this->location);
            }

            require_once($this->location);

            if (!class_exists($this->classname, false)) {
                throw new ClassNotFoundException($this->classname);
            }

            $this->loaded = true;
        }

        /**
         * Return new instance of the RPC module class.
         *
         * @return RPCModule
         */
        public function create() : RPCModule
        {
            return new $this->classname();
        }
    }
?>
