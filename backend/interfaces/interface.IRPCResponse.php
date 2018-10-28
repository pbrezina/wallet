<?php declare(strict_types=1);

    namespace Shockie\Interfaces;

    use Shockie\Interfaces\IRPCProtocol;

    interface IRPCResponse extends IRPCProtocol
    {
        public function getResponse() : object;
    }
?>
