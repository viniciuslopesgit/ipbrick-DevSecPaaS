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
        $ret = new stdClass();
        $ret->result = "-1";
        $ret->description = "Erro ao decodificar JSON: " . json_last_error_msg();
        return json_encode($ret);
    }
    $ret = new stdClass();
    $arguments_count = count((array)$arguments);
    if (!isset($arguments->servername) || !isset($arguments->serveralias) || !isset($arguments->serveradmin) || !isset($arguments->ftplogin) || !isset($arguments->ftppass) || !isset($arguments->documentroot) || !isset($arguments->internet) || !isset($arguments->safe_mode) || !isset($arguments->open_basedir))
    {
        $ret->result = "-1";
        $ret->description = "Invalid parameters! Required fields (servername, serveralias...) are missing.";
        logDebug($ret->description);
        return json_encode($ret);
    }


    $error = [];
    $addWebsite = addWebsite($arguments, $error);
    $addReverseProxy = addReverseProxy($rev_arguments, $error);

    logDebug((" -> Criando reverse-proxy..."));
    logDebug("  [SUCCESS] Site adicionado com sucesso!");
    
    if (!empty($error))
    {
        $ret->result = "-1";
        $ret->description = "Erro ao criar site: " . implode(", ", $error);
        return json_encode($ret);
    }
    $response = $addWebsite ?: ["result" => "0", "description" => "Site criado com sucesso"];
    return json_encode($response);
}