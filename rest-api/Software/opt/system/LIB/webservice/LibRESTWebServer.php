<?php
#########################################################################################################################
##                                                                                                                     ##
##                                                    IPBRICK-OS                                                       ##
##                                                                                                                     ##
##                                                                                                                     ##
##  REST API - LibRESTWebServer                                                       IPBRICK by EXPANDINDUSTRIA 2025  ##
#########################################################################################################################

#########################################################################################################################
##                                                                                                                     ##
##                                                  addRESTWebSite                                                     ##
##                                                                                                                     ##
#########################################################################################################################
function addRESTWebSite($getWebArgs)
{
    logDebug("-> Iniciando addRESTWebSite...");
    global $_path, $bd, $db_server, $db_apache, $db_ftp, $db_named_conf, $db_dns_cname, $db_dns_in_a, $db_user_mail, $db_change, $array_system_users;
    
    include_once ($_path."LIB/LibUsers.php");
    include_once ($_path."LIB/LibError.php");
    include_once ($_path."PHP/IfDBServidor.phpclass");
    include_once ($_path."PHP/IfDBApache.phpclass");
    include_once ($_path."PHP/IfDBFTP.phpclass");
    include_once ($_path."PHP/IfDBNamed_conf.phpclass");
    include_once ($_path."PHP/IfDBDns_cname.phpclass");
    include_once ($_path."PHP/IfDBDns_in_a.phpclass");
    include_once ($_path."PHP/IfDBAlteracao.phpclass");
    include_once ($_path."PHP/IfDBMailutilizador.phpclass");
    include_once ($_path."LIB/LibWebServer.php");
    logDebug("  [OK] Incluindo biblíotecas...");

    $db_server      = new IfDBServidor($bd->conn);
    $db_apache      = new IfDBApache($bd->conn);
    $db_ftp         = new IfDBFTP($bd->conn);
    $db_named_conf  = new IfDBNamed_conf($bd->conn);
    $db_dns_cname   = new IfDBDns_cname($bd->conn);
    $db_dns_in_a    = new IfDBDns_in_a($bd->conn);
    $db_user_mail   = new IfDBMailutilizador($bd->conn);
    $db_change      = new IfDBAlteracao($bd->conn);
    logDebug("  [OK] Populando o banco de dados para o novo hostname...");
    
    $arguments = json_decode($getWebArgs);
    if (json_last_error() !== JSON_ERROR_NONE)
    {
        logDebug("Erro ao processar o JSON! #1");
        $ret = new stdClass();
        $ret->result = "-1";
        $ret->description = "Erro ao decodificar JSON: " . json_last_error_msg();
        return json_encode($ret);
    }
    $ret = new stdClass();
    $arguments_count = count((array)$arguments);
    if (!isset($arguments->servername) || !isset($arguments->serveralias) || !isset($arguments->serveradmin) || !isset($arguments->ftplogin) || !isset($arguments->ftppass) || !isset($arguments->documentroot) || !isset($arguments->internet) || !isset($arguments->safe_mode) || !isset($arguments->open_basedir))
    {

        logDebug("Erro ao processar o JSON! #2");
        $ret->result = "-1";
        $ret->description = "Invalid parameters! Required fields (servername, serveralias...) are missing.";
        logDebug($ret->description);
        return json_encode($ret);
    }
    $error          = [];
    $addWebsite     = addWebsite($arguments, $error);

    $rev_proxy_port                         = "16666"; // Porta Local
    #$arguments_reverse_proxy["idapache"]    = ""; // '1' É reservado para o ipbrick
    $arguments_reverse_proxy["alias"]       = $getWebArgs->serveralias;
    $arguments_reverse_proxy["host"]        = "http://localhost:$rev_proxy_port/";
    $arguments_reverse_proxy["servername"]  = $arguments->servername;
    $reverseproxyargs                       = json_encode($arguments_reverse_proxy);
    if ($reverseproxyargs)
    {
        logDebug(   "-> Criando Reverse Proxy...");
        $addReverse     = addReverseProxy($reverseproxyargs,$error);
    }
    else
        logDebug("  -> Error ao tentar executar addReverseProxy...");
    if (!$error)
        $response = ["result" => $error, "description" => "Website criado com sucesso.\n        Reverse-Proxy criado com sucesso."];
    else
        $response = ["result" => $error, "description"=> "Erro ao criar site."];
    return json_encode($response);
}