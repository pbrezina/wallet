<?php declare(strict_types=1);

    namespace Shockie\Core;

    use Shockie\Core\JSON;
    use Shockie\Interfaces\IHTTPInputMessage;
    use Shockie\Interfaces\IRPCRouter;

    class JSONRPC
    {
        private $router;

        public function __construct(IRPCRouter $router)
        {
            $this->router = $router;
        }

        public function process(IHTTPInputMessage $msg) : string
        {
            $obj = JSON::Parse($msg->getBody());
            $response = $this->router->process($obj);

            return json_encode($response->getResponse());
        }
    }
?>
