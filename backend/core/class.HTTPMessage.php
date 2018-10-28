<?php declare(strict_types=1);

    namespace Shockie\Core;

    use Shockie\Interfaces\IHTTPInputMessage;

    class HTTPMessage implements IHTTPInputMessage
    {
        private $body;

        public function __construct()
        {

        }

        public function setBody(string $body)
        {
            $this->body = $body;
        }

        public function getBody() : string
        {
            return $this->body;
        }

        static public function FromIncomingRequest() : self
        {
            $msg = new HTTPMessage();
            $msg->setBody(file_get_contents('php://input'));

            return $msg;
        }
    }
?>
