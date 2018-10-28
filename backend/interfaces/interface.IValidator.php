<?php declare(strict_types=1);

    namespace Shockie\Interfaces;

    interface IValidator
    {
        public function validate($data) : void;
    }
?>
