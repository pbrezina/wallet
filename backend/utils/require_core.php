<?php declare(strict_types=1);

    /* These are always needed. Autoloading them is unnecessary burden. */
    require_once(__DIR__ . '/../interfaces/interface.IHTTPInputMessage.php');
    require_once(__DIR__ . '/../interfaces/interface.IRPCMethod.php');
    require_once(__DIR__ . '/../interfaces/interface.IRPCModule.php');
    require_once(__DIR__ . '/../interfaces/interface.IRPCProtocol.php');
    require_once(__DIR__ . '/../interfaces/interface.IRPCRequest.php');
    require_once(__DIR__ . '/../interfaces/interface.IRPCRequestData.php');
    require_once(__DIR__ . '/../interfaces/interface.IRPCResponse.php');
    require_once(__DIR__ . '/../interfaces/interface.IRPCRouter.php');
    require_once(__DIR__ . '/../interfaces/interface.ISQLFlavor.php');
    require_once(__DIR__ . '/../interfaces/interface.IValidator.php');

    require_once(__DIR__ . '/../core/class.HTTPMessage.php');
    require_once(__DIR__ . '/../core/class.JSON.php');
    require_once(__DIR__ . '/../core/class.JSONRPC.php');
    require_once(__DIR__ . '/../core/class.JSONSchema.php');
    require_once(__DIR__ . '/../core/class.Module.php');
    require_once(__DIR__ . '/../core/class.RPCProtocol.php');
    require_once(__DIR__ . '/../core/class.RPCError.php');
    require_once(__DIR__ . '/../core/class.RPCMethod.php');
    require_once(__DIR__ . '/../core/class.RPCModule.php');
    require_once(__DIR__ . '/../core/class.RPCRequest.php');
    require_once(__DIR__ . '/../core/class.RPCRequestData.php');
    require_once(__DIR__ . '/../core/class.RPCResponse.php');
    require_once(__DIR__ . '/../core/class.RPCRouter.php');
    require_once(__DIR__ . '/../core/class.SQLResult.php');
    require_once(__DIR__ . '/../core/class.SQLExpression.php');
    require_once(__DIR__ . '/../core/class.SQLQuery.php');
    require_once(__DIR__ . '/../core/class.SQLDelete.php');
    require_once(__DIR__ . '/../core/class.SQLSelect.php');
    require_once(__DIR__ . '/../core/class.SQLUpdate.php');
    require_once(__DIR__ . '/../core/class.SQLInsert.php');
    require_once(__DIR__ . '/../core/class.SQLDatabase.php');
    require_once(__DIR__ . '/../core/class.System.php');
?>
