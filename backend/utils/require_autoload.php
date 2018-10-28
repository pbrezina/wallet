<?php declare(strict_types=1);

    function shockie_autoload(string $fqn) : void {
        $parts = explode('\\', $fqn);

        if ($parts[0] != 'Shockie') {
            return;
        }

        switch ($parts[1]) {
        case 'Exceptions':
            require_once(__DIR__ . '/../exceptions/class.' . $parts[2] . '.php');
            return;
        case 'Interfaces':
            require_once(__DIR__ . '/../interfaces/interface.' . $parts[2] . '.php');
            return;
        case 'Core':
            require_once(__DIR__ . '/../core/class.' . $parts[2] . '.php');
            return;
        default:
            return;
        }
    }

    spl_autoload_register('shockie_autoload');
?>
