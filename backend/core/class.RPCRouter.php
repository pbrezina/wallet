<?php declare(strict_types=1);

    namespace Shockie\Core;

    use Throwable;
    use Shockie\Core\RPCError;
    use Shockie\Core\RPCRequest;
    use Shockie\Core\RPCRequestData;
    use Shockie\Core\RPCResponse;
    use Shockie\Core\RPCRouterModule;
    use Shockie\Exceptions\RPCUnknownModuleException;
    use Shockie\Interfaces\IRPCResponse;
    use Shockie\Interfaces\IRPCRouter;

    /**
     * Remote Procedure Call Router.
     *
     * Transfers input message to correct module and calls the requested
     * method.
     */
    class RPCRouter implements IRPCRouter
    {
        private $modules;

        /**
         * Process incomming message.
         *
         * This will invoke the requested method call and return its response.
         *
         * @param object $msg Input message.
         * @return IRPCResponse In case of a successful method call, this is
         *         an instance of RPCResponse. If the call thrown an error,
         *         instance of RPCError is returned.
         *
         */
        public function process(object $msg) : IRPCResponse
        {
            try {
                $request = new RPCRequest($msg);
            } catch (Throwable $error) {
                return new RPCError(null, $error);
            }

            try {
                $data = new RPCRequestData($request);
                $module = $this->find($request->getModuleName());
                if ($module === null) {
                    throw new RPCUnknownModuleException($request->getModuleName());
                }

                $module->load();
                $result = $module->create()->call($data);

                $response = new RPCResponse($request, $result);
            } catch (Throwable $error) {
                $response = new RPCError($request, $error);
            }

            return $response;
        }

        /**
         * Register an RPC module with the router.
         *
         * @param string $name Module name.
         * @param string $path Path to the file that includes the module.
         * @param string $classname Name of the class that implements the module.
         * @param boolean $enabled True if the module is enabled.
         * @return void
         */
        public function register(string $name,
                                 string $path,
                                 string $classname,
                                 bool $enabled) : void
        {
            $this->modules[$name] = new RPCRouterModule(
                $name, $path, $classname, $enabled
            );
        }

        private function find($name) : ?RPCRouterModule
        {
            if (!isset($this->modules[$name])) {
                return null;
            }

            return $this->modules[$name];
        }
    }
?>
