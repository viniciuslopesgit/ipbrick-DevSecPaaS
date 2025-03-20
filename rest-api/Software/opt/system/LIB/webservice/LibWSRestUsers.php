<?php
#########################################################################################################################
##                                                                                                                     ##
##                                                    IPBRICK-OS                                                       ##
##                                                                                                                     ##
##                                                                                                                     ##
##  REST API - LibWSRestUsers                                                         IPBRICK by EXPANDINDUSTRIA 2025  ##
#########################################################################################################################
function getUsersRESTAPI($getUsersArgs)
{
    logDebug(" ### Iniciando getUsersAPI...");

    $arguments = json_decode($getUsersArgs);
    $ret = new stdClass();

    $arguments_count = count((array) $arguments);
    for ($i = 0; $i < $arguments_count; $i++) {
        if (!isset($arguments[$i]->login_auth) || !isset($arguments[$i]->password_auth)) {
            $ret->result = "-1";
            $ret->description = "Invalid arguments";
            return $ret;
        }
    }
    $users = getSystemUsers();
    $response[] = $users;
    return json_encode($response);
}