<?php declare(strict_types=1);

    namespace Shockie\Interfaces;

    use Shockie\Interfaces\IRPCProtocol;

    interface IRPCRequest extends IRPCProtocol
    {
        public function getData();
    }
?>
