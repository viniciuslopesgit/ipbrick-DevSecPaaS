<?php
#########################################################################################################################
##                                                                                                                     ##
##                                                    IPBRICK-OS                                                       ##
##                                                                                                                     ##
##                                                                                                                     ##
##  REST API - LibRESTCertificates                                                    IPBRICK by EXPANDINDUSTRIA 2025  ##
#########################################################################################################################

function GetRESTCertificates()
{
    global $path, $bd;
    global $db_server, $db_certs, $array_system_users;

    logDebug("  -> Iniciando getRestCertificates...");
    include_once($path."PHP/IfDBServidor.phpclass");
    include_once($path."PHP/IfDBCertificado.phpclass");
    include_once($path."LIB/LibCertificates.php");

    $db_server  = new IfDBServidor($bd->conn);
    $db_certs   = new IfDBCertificado($bd->conn);

    $error = [];
    $getCerts = getCertificates();
    $response = $getCerts ?: ["result" => "0", "description" => "Certificados gerados com sucesso!"]; 
    return json_encode($response);
}