<?php declare(strict_types=1);

    namespace Shockie\Interfaces;

    use Shockie\Interfaces\IRPCResponse;

    interface IRPCRouter
    {
        public function process(object $msg) : IRPCResponse;
    }
?>
