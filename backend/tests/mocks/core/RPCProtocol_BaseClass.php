<?php declare(strict_types=1);

    namespace Shockie\Tests\Mocks\Core;

    use Shockie\Core\RPCProtocol;

    final class RPCProtocol_BaseClass extends RPCProtocol
    {
        public function __construct(int $protocol_version,
                                    string $type,
                                    string $module,
                                    string $method,
                                    int $method_version)
        {
            parent::__construct($protocol_version,
                                $type,
                                $module,
                                $method,
                                $method_version);
        }

        public function getAsObject() : object
        {
            return parent::getAsObject();
        }
    }
?>
