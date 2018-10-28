<?php declare(strict_types=1);

    function shockie_autoload_tests(string $fqn) : void {
        $parts = explode('\\', $fqn);

        if ($parts[0] != 'Shockie') {
            return;
        }

        switch ($parts[1]) {
        case 'Tests':
            switch ($parts[2]) {
            case 'Core':
                require_once(__DIR__ . '/core/' . $parts[3] . '.php');
                return;
            case 'Mocks':
                switch ($parts[3]) {
                    case 'Core':
                        require_once(__DIR__ . '/mocks/core/' . $parts[4] . '.php');
                        return;
                    default:
                        return;
                }
            default:
                return;
            }
        default:
            return;
        }
    }

    spl_autoload_register('shockie_autoload_tests');

    require_once(__DIR__ . '/../utils/require_autoload.php');
?>
