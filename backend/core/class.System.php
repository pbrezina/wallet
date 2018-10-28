<?php declare(strict_types=1);

    namespace Shockie\Core;

    use InvalidArgumentException;
    use Shockie\Core\JSON;
    use Shockie\Core\JSONSchema;
    use Shockie\Core\SQLDatabase;
    use Shockie\Core\MySQLDatabase;

    class System
    {
        private $config;
        private $database;

        /**
         * Process system configuration.
         *
         * @param object $config Configuration object that must validate against
         *                       ./schemas/schema.config.json
         */
        public function __construct(object $config)
        {
            $schema = new JSONSchema('./schemas/schema.config.json');
            $schema->validate($config);

            $this->config = $config;
            $this->database = $this->connectToDatabase($this->config->database);
        }

        /**
         * Return system wide database connection.
         *
         * @return SQLDatabase
         */
        public function getDatabaseConnection() : SQLDatabase
        {
            return $this->database;
        }

        /**
         * Return list of configured RPC modules.
         *
         * The returned objects are in the following format:
         *   {
         *     "name": string (Name of the module),
         *     "file": string (File that contains this module),
         *     "class": string (Class name that implements this module),
         *     "enabled": boolean (True if the module is enabled)
         *    }
         *
         * @return object[]
         */
        public function getRPCModules() : array
        {
            return $this->config->rpc->modules;
        }

        private function connectToDatabase(object $dbconfig) : SQLDatabase
        {
            switch ($dbconfig->driver) {
            case "mysql":
                $spec = [
                    'host' => $dbconfig->host,
                    'port' => $dbconfig->port,
                    'dbname' => $dbconfig->dbname,
                    'unix_socket' => $dbconfig->unix_socket,
                    'charset' => $dbconfig->charset
                ];

                return new MySQLDatabase(
                    $spec, $dbconfig->username, $dbconfig->password
                );
                break;
            default:
                throw new InvalidArgumentException(
                    'Unknown database driver: ' . $dbconfig->driver
                );
            }
        }

        /**
         * Read configuration from a file in JSON format.
         *
         * @param string $configFile Path to the file.
         * @return void
         */
        static public function FromFile(string $configFile)
        {
            return new self(JSON::ParseFromFile($configFile));
        }
    }
?>
