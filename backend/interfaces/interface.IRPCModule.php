<?php declare(strict_types=1);

    namespace Shockie\Interfaces;

    use Shockie\Interfaces\IRPCRequestData;

    interface IRPCModule
    {
        public function call(IRPCRequestData $rpcdata);
    }
?>
