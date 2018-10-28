<?php declare(strict_types=1);

    /* Enable error reporting. */
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);

    /* Require fundamental classes. Everything else will be autoloaded. */
    require_once(__DIR__ . '/utils/require_core.php');
    require_once(__DIR__ . '/utils/require_autoload.php');

    use Shockie\Core\System;
    use Shockie\Core\RPCRouter;
    use Shockie\Core\HTTPMessage;
    use Shockie\Core\JSONRPC;
    use Shockie\Core\RPCError;

    try {
        $system = System::FromFile('./config.json');
        $router = new RPCRouter();
        foreach ($system->getRPCModules() as $module) {
            $router->register(
                $module->name, $module->file, $module->class, $module->enabled
            );
        }

        if (isset($_GET['mock'])) {
            header('Content-Type: application/json');
            $message = new HTTPMessage();
            $message->setBody('');
        } else {
            $message = HTTPMessage::FromIncomingRequest();
        }

        $rpc = new JSONRPC($router);
        echo $rpc->process($message);
    } catch (Throwable $error) {
        $reply = new RPCError(null, $error);
        echo json_encode($reply->getResponse());
    }
?>
