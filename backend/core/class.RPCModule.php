<?php declare(strict_types=1);

    namespace Shockie\Core;

    use Closure;
    use Throwable;
    use Shockie\Core\Module;
    use Shockie\Exceptions\RPCCallException;
    use Shockie\Exceptions\RPCInvalidVersionException;
    use Shockie\Exceptions\RPCUnknownMethodException;
    use Shockie\Interfaces\IRPCMethod;
    use Shockie\Interfaces\IRPCModule;
    use Shockie\Interfaces\IRPCRequestData;

    /**
     * Base class for RPC modules.
     */
    abstract class RPCModule extends Module implements IRPCModule
    {
        private $methods;

        /**
         * Construct an RPC module.
         *
         * @param RPCMethod[] $methods RPC methods defined in this module.
         */
        public function __construct(array $methods)
        {
            parent::__construct();

            $this->methods = $methods;
        }

        /**
         * Call RPC method based on input data.
         *
         * @param IRPCRequestData $rpcdata Input RPC protocol data.
         * @return mixed
         */
        public function call(IRPCRequestData $rpcdata)
        {
            $name = $rpcdata->getMethodName();
            $version = $rpcdata->getMethodVersion();
            $method = $this->find($name, $version);

            try {
                return $method->call($rpcdata);
            } catch (Throwable $error) {
                throw new RPCCallException($name, $version, $error);
            }
        }

        /**
         * Create closure from method name to be used as RPC method handler.
         *
         * @param string $name PHP method name.
         * @return closure
         *
         * @example $this->rpc('rpc_test') where rpc_test is a protected method
         *          of derived class.
         */
        protected function rpc(string $name) : closure
        {
            return Closure::fromCallable([$this, $name]);
        }

        private function find(string $name, int $version) : IRPCMethod
        {
            $wrongversion = false;

            foreach ($this->methods as $method) {
                if ($method->getName() == $name) {
                    if ($method->getVersion() != $version) {
                        $wrongversion = true;
                        continue;
                    }

                    return $method;
                }
            }

            if ($wrongversion) {
                throw new RPCInvalidVersionException($name, $version);
            }

            throw new RPCUnknownMethodException($name);
        }
    }
?>
