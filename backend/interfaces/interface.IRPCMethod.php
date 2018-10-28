<?php declare(strict_types=1);

    namespace Shockie\Interfaces;

    use Shockie\Interfaces\IRPCRequestData;

    interface IRPCMethod
    {
        public function getName() : string;
        public function getVersion() : int;
        public function call(IRPCRequestData $rpcdata);
    }
?>
