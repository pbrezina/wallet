<?php declare(strict_types=1);

    namespace Shockie\Interfaces;

    interface IRPCProtocol
    {
        const REQUEST = "request";
        const RESPONSE = "response";
        const ERROR = "error";

        public function getProtocolVersion() : int;
        public function getType() : string;
        public function getModuleName() : string;
        public function getMethodName() : string;
        public function getMethodVersion() : int;
    }
?>
