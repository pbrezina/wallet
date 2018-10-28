<?php declare(strict_types=1);

    namespace Shockie\Interfaces;

    interface IRPCRequestData
    {
        public function getRequest() : IRPCRequest;
        public function getMethodName() : string;
        public function getMethodVersion() : int;
        public function getData();
    }
?>
