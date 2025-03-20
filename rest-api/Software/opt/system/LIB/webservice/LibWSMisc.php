<?php

/**
 * Authenticate User
 *
 * @access private
 * @param string $login
 * @param string $password
 * @return int
 */
function authRequest($login, $password, $internal='0')
{
    global $dbvalida;
    global $_xmlrpc_default_login;
    global $_xmlrpc_default_key;
    global $_srv_auth;
    global $_domadmin;
    global $_domadminpass;
    global $bindldap; 
    global $_path;
    global $bd;

    require_once $_path."PHP/IfDBsystemconfig.phpclass";
    $dbipbrickconfig = new IfDBIpbrickconfig($bd->conn);
    
    $password = base64_decode($password);

    $remoteip = $_SERVER["REMOTE_ADDR"];
    $api_key = $dbipbrickconfig->getIpbrickconfigByIdipbrickconfig (13); //password criada quando se ativa a API REST
    $api_key_value =$api_key[0]->valor;

    if ($login == $_xmlrpc_default_login || $login == "SoapClient") {
        //Autenticação padrão dos servidores ipbrick
        //FIX IT
        if ($password == $_xmlrpc_default_key || $password == $api_key_value) {
            return 1;
        } else {
            return -1;
        }
    } else if (!$internal) {
        if ($_srv_auth == "winbind" || $_srv_auth == "admember" || $_srv_auth == "admemberslave") {
            if ($_domadmin == $login && $_domadminpass == $password) {
                return 1;
            } else {
                return -1;
            }
        } else {
            $auth = $bindldap->bind ($login, $password);
            if ($auth) {
                $data = $bindldap->search ("ou=Groups","(gidNumber=512)",array('memberUid'));
                for ($i=0; $i < $data[0]['memberuid']["count"]; $i++) {
                    if ($data[0]["memberuid"][$i] == $login) {
                        return 1;
                    }   
                }
                return -1;
            } else {
                return -1;
            }
        }
    }
    return -1;
}


/**
 * Authenticate User without administrative previleges
 *
 * @access private
 * @param string $login
 * @param string $password
 * @return int
 */
function authRequestUser($login, $password)
{
    global $dbvalida;
    global $_xmlrpc_default_login;
    global $_xmlrpc_default_key;
    global $_srv_auth;
    global $_domadmin;
    global $_domadminpass;

    $password = base64_decode($password);

    $remoteip = $_SERVER["REMOTE_ADDR"];
    if ($login == $_xmlrpc_default_login) {
        //Autenticação padrão dos servidores ipbrick
        //FIX IT
        if ($password == $_xmlrpc_default_key) {
            return 1;
        } else {
            return -1;
        }
    } else if ($_srv_auth == "winbind" || $_srv_auth == "admember" || $_srv_auth == "admemberslave") {
        if ($_domadmin == $login && $_domadminpass == $password) {
            return 1;
        } else {
            return -1;
        }
    } else {
        $userinfo = getSystemUserInfoByLogin($login);
        if ($userinfo[0]->login == $login && $userinfo[0]->password == $password) {
            $domainadminsinfo = getSystemGroupInfoByGidnumber(513);
            $n_member = count((array)$domainadminsinfo[0]->member);
            for ($i = 0; $i < $n_member; $i++) {
                if ($domainadminsinfo[0]->member[$i]->login == $login) {
                    return 1;
                }
            }
            return -1;
        } else {
            return -1;
        }
    }
    return -1;
}

/**
 * Get Autentication type
 *
 * Returns an array with IPBrick authentication data
 * @access public
 * @param string $arguments
 * @return string
 */
function getWSAuthenticationData($arguments)
{
    global $_ws_debug;

    $arguments = json_decode($arguments, true);

    if ($_ws_debug == 1) {
        error_log("Function: addWSuser\n", 3, "/tmp/systemws.log");
        error_log(print_r($arguments, true) . "\n", 3, "/tmp/systemws.log");
    }
    $authlogin = $arguments["auth"]["login"];
    $authpassword = $arguments["auth"]["password"];
    $authentication = authRequest($authlogin, $authpassword, '1');

    $ret["coderesult"] = '-1';
    $ret["result"] = "";

    if ($authentication != 1) {
        $ret["coderesult"] = '-1';
        $ret["result"] = 'Login validation error';
        return json_encode($ret);
    }
    $result = getAuthenticationData ();
    $array_result = get_object_vars($result[0]);
    $ret["result"] = $array_result;
    $ret["coderesult"] = '1';
    return json_encode($ret);
}

/**
 * Get IPBrick language
 *
 * Returns an array with IPBrick active language
 * @access public
 * @param string $arguments
 * @return string
 */
function getWSIPBrickLanguage($arguments)
{
    global $_ws_debug;

    $arguments = json_decode($arguments, true);

    if ($_ws_debug == 1) {
        error_log("Function: addWSuser\n", 3, "/tmp/systemws.log");
        error_log(print_r($arguments, true) . "\n", 3, "/tmp/systemws.log");
    }
    $authlogin = $arguments["auth"]["login"];
    $authpassword = $arguments["auth"]["password"];
    $authentication = authRequest($authlogin, $authpassword);

    $ret["coderesult"] = '-1';
    $ret["result"] = "";

    if ($authentication != 1) {
        $ret["coderesult"] = '-1';
        $ret["result"] = 'Login validation error';
        return json_encode($ret);
    }
    $result = getIPBrickLanguage();
    $array_result = get_object_vars($result[0]);
    $ret["result"] = $array_result;
    $ret["coderesult"] = '1';
    return json_encode($ret);
}



/**
 * Check IPBrick license
 *
 * Returns an array with IPBrick license - type, module, days to expire
 * @access public
 * @param string $arguments
 * @return string
 */
function checkWSIPBrickLicense($arguments)
{
    global $_ws_debug, $__system;

    $arguments = json_decode($arguments, true);

    if ($_ws_debug == 1) {
        error_log("Function: checkWS".$__system."License\n", 3, "/tmp/systemws.log");
        error_log(print_r($arguments, true) . "\n", 3, "/tmp/systemws.log");
    }
    $authlogin = $arguments["auth"]["login"];
    $authpassword = $arguments["auth"]["password"];
    $authentication = authRequest($authlogin, $authpassword);

    $ret["coderesult"] = '-1';
    $ret["result"] = "";

    if ($authentication != 1) {
        $ret["coderesult"] = '-1';
        $ret["result"] = 'Login validation error';
        return json_encode($ret);
    }
      
    $ret["result"] = checkIPBrickLicense ();
    $ret["coderesult"] = '1';
    return json_encode($ret);
}


/**
 * Get IPBrick Licence (method used on NUSOAP)
 *
 * Returns an array with licence info
 * 
 * Return -1 - Invalid Credentials
 * Return 1 - Success
 * 
 * @access public
 * @param string $apiAccessLogin
 * @param string $apiAccessPass
 * @return array
 */
function getWSSystemLicense($apiAccessLogin,$apiAccessPass)
{
    global $_ws_debug;
    global $only_necessary_files;
        
    //Load only necessary files and init necessry classes
    if ($only_necessary_files) {
        global $_path;
        global $licence_limits;

        include_once $_path.'LIB/LibMisc.php';
        
        $licence_limits = getLicenceLimits();
    }

    if ($_ws_debug == 1) {
        error_log("Function: getWSSystemLicense\n", 3, "/tmp/systemws.log");
        error_log(print_r("API Access Login: ".$apiAccessLogin, true) . "\n", 3, "/tmp/systemws.log");
        error_log(print_r("API Access Password: ".$apiAccessPass, true) . "\n", 3, "/tmp/systemws.log");
    }
    $authlogin = $apiAccessLogin;
    $authpassword = $apiAccessPass;
    
    $authentication = authRequest($authlogin, $authpassword);

    $ret["result"]["resultcode"] = '-1';

    if ($authentication != 1) {
        $ret["result"]["resultcode"] = '-1';
        $ret["result"]["errorcode"][] = 1063;
        return $ret;
    }
          
    
    $ret["result"]["resultcode"] = 1;
    $ret["result"]["errorcode"][] = '';
    $ret["licence"] = checkIPBrickLicense ();

    return $ret;
}




/**
 * Validates the user password status
 *
 * Returns an array with IPBrick password status
 * 
 * Return -1 - Invalid Credentials
 * Return 1 - Valid Credentials
 * Return 2 - Password Expired
 * Return 3 - Account Locked
 * 
 * @access public
 * @param string $arguments
 * @return string
 */
function validateWSLoginCredentials($arguments)
{
    global $_ws_debug;
    
    $arguments = json_decode($arguments, true);

    if ($_ws_debug == 1) {
        error_log("Function: validateWSLoginCredentials\n", 3, "/tmp/systemws.log");
        error_log(print_r($arguments, true) . "\n", 3, "/tmp/systemws.log");
    }
    $authlogin = $arguments["auth"]["login"];
    $authpassword = $arguments["auth"]["password"];
    $authentication = authRequest($authlogin, $authpassword);

    $ret["coderesult"] = '-1';
    $ret["result"] = "";

    if ($authentication != 1) {
        $ret["coderesult"] = '-1';
        $ret["result"] = 'Login validation error';
        return json_encode($ret);
    }
      
    $ret["result"] = validateLoginCredentials($arguments["args"]["login"], $arguments["args"]["password"]);
    $ret["coderesult"] = '1';
    return json_encode($ret);
}



/**
 * Get remote server passwords
 *
 * Returns an array with remote server passwords
 * 
 * Return -1 - Invalid Credentials
 * Return 1 - Success
 * 
 * @access public
 * @param string $arguments
 * @return string
 */
function getWSPasswords($arguments) {
    global $_ws_debug;

    $arguments = json_decode($arguments, true);

    if ($_ws_debug == 1) {
        error_log("Function: getWSPasswords\n", 3, "/tmp/systemws.log");
        error_log(print_r($arguments, true) . "\n", 3, "/tmp/systemws.log");
    }
    $authlogin = $arguments["auth"]["login"];
    $authpassword = $arguments["auth"]["password"];
    $authentication = authRequest($authlogin, $authpassword);

    $ret["coderesult"] = '-1';
    $ret["result"] = "";

    if ($authentication != 1) {
        $ret["coderesult"] = '-1';
        $ret["result"] = 'Login validation error';
        return json_encode($ret);
    }

    $ret["result"] = getPasswords ();
    $ret["coderesult"] = '1';
    return json_encode($ret);
}

/**
 * Get remote server system passwords
 *
 * Returns an array with remote server passwords
 * 
 * Return -1 - Invalid Credentials
 * Return 1 - Success
 * 
 * @access public
 * @param string $arguments
 * @return string
 */
function getWSSystemPasswords($arguments) {
    global $_ws_debug;

    $arguments = json_decode($arguments, true);

    if ($_ws_debug == 1) {
        error_log("Function: getWSPasswords\n", 3, "/tmp/systemws.log");
        error_log(print_r($arguments, true) . "\n", 3, "/tmp/systemws.log");
    }
    $authlogin = $arguments["auth"]["login"];
    $authpassword = $arguments["auth"]["password"];
    $authentication = authRequest($authlogin, $authpassword);

    $ret["coderesult"] = '-1';
    $ret["result"] = "";

    if ($authentication != 1) {
        $ret["coderesult"] = '-1';
        $ret["result"] = 'Login validation error';
        return json_encode($ret);
    }
    $file_server = file_get_contents("/etc/server-id.bin");
    $ret["result"] = base64_encode($file_server);
    $ret["coderesult"] = '1';
    return json_encode($ret);
}


/**
 * Get IBQuota - Get Print Quota User
 *
 * Returns an array with Print Quota User info
 * @access public
 * @param string $apiAccessLogin
 * @param string $apiAccessPass
 * @param string $user
 * @param 
 * @return array
 */
function getWSPrintQuotaByUser($apiAccessLogin,$apiAccessPass,$user)
{
    global $_ws_debug;
    global $only_necessary_files;
    
    global $ibquota_pkg;
    global $dbvoipmysqlibquota;
    
    //Load only necessary files and init necessry classes
    if ($only_necessary_files) {
        global $ibquota_pkg;
        global $_path;
        global $_dbhost,$port_mysql, $user_mysql_ibquota, $passwd_mysql_ibquota,$dbname_ibquota;

        require_once $_path."PHP/IfDBVoIPMysql.phpclass";
        require_once $_path."PHP/ligamysql.phpclass";
    
        if ($ibquota_pkg=="sim") {
            $dbmysqlibquota = new ligamysql($_dbhost,$port_mysql, $user_mysql_ibquota, $passwd_mysql_ibquota,$dbname_ibquota);
            $dbvoipmysqlibquota= new IfDBVoIPMysql($dbmysqlibquota->conn);
        }
    }

    if ($_ws_debug == 1) {
        error_log("Function: getWSPrintQuotaByUser\n", 3, "/tmp/systemws.log");
        error_log(print_r("API Access Login: ".$apiAccessLogin, true) . "\n", 3, "/tmp/systemws.log");
        error_log(print_r("API Access Password: ".$apiAccessPass, true) . "\n", 3, "/tmp/systemws.log");
        error_log("Arguments: ".print_r($user, true) . "\n", 3, "/tmp/systemws.log");
    }
    $authlogin = $apiAccessLogin;
    $authpassword = $apiAccessPass;
    $login = $user;
    
    $authentication = authRequest($authlogin, $authpassword);

    $ret["result"]["resultcode"] = '-1';

    if ($authentication != 1) {
        $ret["result"]["resultcode"] = '-1';
        $ret["result"]["errorcode"][] = 1063;
        return $ret;
    }

    if($ibquota_pkg=="sim") {
        $user_print_ops = $dbvoipmysqlibquota->getPrintQuotaByUser($login);
        $ret["result"]["resultcode"] = 1;
        $ret["result"]["errorcode"][] = '';
        $ret["quota"] = $user_print_ops[0]->quota;
        
    } else {
        $ret["result"]["resultcode"] = '-1';
        $ret["result"]["errorcode"][] = 1170;
    }  

    return $ret;
}


/**
 * Get DNS SRV Records
 *
 * Returns an array with DNS SRV Records
 * 
 * Return -1 - Invalid Credentials
 * Return 1 - Success
 * 
 * @access public
 * @param string $apiAccessLogin
 * @param string $apiAccessPass
 * @param string $service
 * @param string $zone
 * @return array
 */
function getWSSystemRoles ($apiAccessLogin, $apiAccessPass, $service, $zone) {
    global $_ws_debug;
    global $only_necessary_files;

    if ($_ws_debug == 1) {
        error_log ("Function: getWSSystemRoles\n", 3, "/tmp/systemws.log");
        error_log(print_r("API Access Login: ".$apiAccessLogin, true) . "\n", 3, "/tmp/systemws.log");
        error_log(print_r("API Access Password: ".$apiAccessPass, true) . "\n", 3, "/tmp/systemws.log");
        error_log("Arguments: ".$service. " ".$zone."\n", 3, "/tmp/systemws.log");
    }
    if ($only_necessary_files) {
        global $_path;
        global $_dbhost;
        global $_dbport;
        global $_dbname;
        global $_dbpass;
        global $_dbuser;
        global $dbautenticacao;
        require_once $_path.'LIB/LibTopology.php';
        require_once $_path."PHP/IfDBAutenticacao.phpclass";


        $bd = new ligabd ($_dbhost, $_dbport, $_dbname, $_dbpass, $_dbuser);
        $dbautenticacao = new IfDBAutenticacao($bd->conn);
    }

    $authlogin = $apiAccessLogin;
    $authpassword = $apiAccessPass;

    $authentication = authRequest($authlogin, $authpassword);

    $ret["result"]["resultcode"] = '-1';

    if ($authentication != 1) {
        $ret["result"]["resultcode"] = '-1';
        $ret["result"]["errorcode"][] = 1063;
        return $ret;
    }

    $result =  getSystemRoles($service, $zone);

    $ret["result"]["resultcode"] = 1;
    $ret["result"]["errorcode"][] = '';
    $ret["systemroles"] = $result;
    return $ret;
}


/**
 * Get WebServer ServerName 
 *
 * Returns an array with ServerName
 * 
 * Return -1 - Invalid Credentials
 * Return 1 - Success
 * 
 * @access public
 * @param string $apiAccessLogin
 * @param string $apiAccessPass
 * @param int $idapache
 * @return array
 */
function getWSWebServerNameByIdapache ($apiAccessLogin, $apiAccessPass, $idapache) {
    global $_ws_debug;
    global $only_necessary_files;

    if ($_ws_debug == 1) {
        error_log ("Function: getWSWebServerInfoByIdapache\n", 3, "/tmp/systemws.log");
        error_log(print_r("API Access Login: ".$apiAccessLogin, true) . "\n", 3, "/tmp/systemws.log");
        error_log(print_r("API Access Password: ".$apiAccessPass, true) . "\n", 3, "/tmp/systemws.log");
        error_log("Arguments: ".$idapache."\n", 3, "/tmp/systemws.log");
    }
    if ($only_necessary_files) {
        global $_path;
        global $_dbhost;
        global $_dbport;
        global $_dbname;
        global $_dbpass;
        global $_dbuser;
        global $dbapache;
        include_once ($_path."PHP/IfDBApache.phpclass");

        $bd = new ligabd ($_dbhost, $_dbport, $_dbname, $_dbpass, $_dbuser);
        $dbapache = new IfDBApache ($bd->conn);

        require_once $_path.'LIB/LibWebServer.php';
        require_once $_path.'LIB/LibNetwork.php';
    }

    $authlogin = $apiAccessLogin;
    $authpassword = $apiAccessPass;

    $authentication = authRequest($authlogin, $authpassword);

    $ret["result"]["resultcode"] = '-1';

    if ($authentication != 1) {
        $ret["result"]["resultcode"] = '-1';
        $ret["result"]["errorcode"][] = 1063;
        return $ret;
    }

    $result = getWebServerInfoByIdapache ($idapache);

    $ret["result"]["resultcode"] = 1;
    $ret["result"]["errorcode"][] = '';
    $ret["servername"] = $result[0]->servername;
    return $ret;
}

 /**
  * Get Updates, Version and Compatibility mode status
  *
  * Returns an array with bugfixes ,version and modocomp_status
  * @access public
  * @param string[] $arguments
  * @return string
  */
function getWSOficialUpdates ($arguments)
{
    global $_ws_debug;
    global $dbbugfixes;
    global $dbmodocomp;
    global $dbsrvipbrick;

    $authlogin = $arguments[0];
    $authpassword = $arguments[1];
    
    $authentication = authRequest ($authlogin, $authpassword);
    
    $ret["coderesult"] = '-1';
    $ret["result"] = "";
    
    if ($authentication != 1) {
        $ret["coderesult"] = '-1';
        $ret["result"] = 'Login validation error';
        utf8_encode_array ($ret);
        return json_encode ($ret);
    }
    
    $bugfixes = $dbbugfixes->getBugfixes ();
    $modocomp = $dbmodocomp->getRecords ();
    $modocomp_status = $dbmodocomp->getModeCompStatus ();
    if ($_ws_debug == 1) {
        error_log ("Function: getWSOficialUpdates\n", 3, "/tmp/systemws.log");
        error_log ("bugfixes: ".print_r ($bugfixes, true)."\n", 3, "/tmp/systemws.log");
        error_log ("modocomp: ".print_r ($modocomp, true)."\n", 3, "/tmp/systemws.log");
        error_log ("modocomp_status: ".print_r ($modocomp_status, true)."\n", 3, "/tmp/systemws.log");
    }
    $array_bugfixes = array ();
    $array_modocomp = array ();
    $array_modocomp_status = array ();
    $n_bugfixes = count ((array)$bugfixes);
    $n_modocomp = count ((array)$modocomp);

    for ($i = 0; $i < $n_bugfixes; $i++) {
        $array_bugfixes[$i] = get_object_vars ($bugfixes[$i]);
    }

    for ($i = 0; $i < $n_modocomp; $i++) {
        $srvinfo = $dbsrvipbrick->getSrvipbrickByIdsrvipbrick ($modocomp[$i]->idsrvipbrick);
        $modocomp[$i]->tipo = $srvinfo[0]->tipo;
        $array_modocomp[$i] = get_object_vars ($modocomp[$i]);
    }

    $array_modocomp_status = get_object_vars ($modocomp_status[0]);

    $ret["result"]["bugfixes"] = $array_bugfixes;
    $ret["result"]["modocomp"] = $array_modocomp;
    $ret["result"]["modocomp_status"] = $array_modocomp_status;

    $ret["coderesult"] = '1';
    utf8_encode_array ($ret);
    return json_encode ($ret);
}

/**
 * Get chat history 
 *
 * Returns an array with ServerName
 * 
 * 
 * @access public
 * @param string $apiAccessLogin
 * @param string $apiAccessPass
 * @param string $timestamp
 * @return array
 */
function getWSChatHistory ($apiAccessLogin, $apiAccessPass, $timestamp) {
    global $_ws_debug;
    global $only_necessary_files;
    global $_xmlrpc_default_login, $_xmlrpc_default_key;

    if ($_ws_debug == 1) {
        error_log ("Function: getWSChatHistory\n", 3, "/tmp/systemws.log");
        error_log(print_r("API Access Login: ".$apiAccessLogin, true) . "\n", 3, "/tmp/systemws.log");
        error_log(print_r("API Access Password: ".$apiAccessPass, true) . "\n", 3, "/tmp/systemws.log");
        error_log("Arguments: ".$timestamp."\n", 3, "/tmp/systemws.log");
    }
    if ($only_necessary_files) {
        global $_path;
        include_once $_path."PHP/IfDBIpSocket.phpclass";

        require_once $_path.'LIB/LibMisc.php';
    }

    $authlogin = $apiAccessLogin;
    $authpassword = base64_decode($apiAccessPass);

    //$authentication = authRequest($authlogin, $authpassword);
    //Only check and authorize soap key
    $authentication = -1;
    if ($authlogin == $_xmlrpc_default_login) {
        if ($authpassword == $_xmlrpc_default_key) {
            $authentication = 1;
        } else {
            $authentication = -1;
        }
    }

    $ret["result"]["resultcode"] = '-1';

    if ($authentication != 1) {
        $ret["result"]["resultcode"] = '-1';
        $ret["result"]["errorcode"][] = 1063;
        return $ret;
    }

    $result = getChatHistory ($timestamp);

    $ret["result"]["resultcode"] = 1;
    $ret["result"]["errorcode"][] = '';
    $ret["chathistory"] = json_encode($result);
    return $ret;
}



/**
 * Get SOGO events from user between dates
 *
 * Returns an array with user events
 * 
 * Return -1 - Invalid Credentials
 * Return 1  - Success
 * 
 * @access public
 * @param array $events_to_search
 *   $events_to_search = [];
     $events_to_search ['sogo_admin_login']    = "administrator";
     $events_to_search ['sogo_admin_password'] = "R0laBill";
     $events_to_search ['user']                = "pedro";
     $events_to_search ['date_start']          = "20210620";
     $events_to_search ['date_end']            = "20210625";
 * 
 * @return array
 */

function ws_log($msg)
{
    $log_file="/tmp/systemws.log";
    if (is_file ($log_file))   error_log($msg , 3, $log_file);
}

function getWSCalendar ($apiAccessLogin, $apiAccessPass, $events_to_search)
{
    global $only_necessary_files;

    $authlogin = $apiAccessLogin;
    $authpassword = $apiAccessPass;
            
    
    ws_log ("Function: getWSCalendar\n");
    ws_log(print_r("API Access Login: ".$apiAccessLogin, true) . "\n");
    ws_log(print_r("API Access Password: ".$apiAccessPass, true) . "\n");
    ws_log("\n\n");
    ws_log("Variable events_to_search encoded: ".print_r ($events_to_search, true)."\n");
    ws_log("\n\n");   
    
    $authentication = authRequest ($authlogin, $authpassword);
    $ret["result"]["resultcode"] = '-1';
    if ($authentication != 1) {
        $ret["result"]["resultcode"] = '-1';
        $ret["result"]["errorcode"][] = 1063;
        ws_log("AuthRequest failed\n");
        ws_log("Ret NOK: ".print_r ($ret, true)."\n");

        return $ret;
    }

    $tmp_events_to_search =  json_decode($events_to_search) ;
    if (json_last_error() == JSON_ERROR_NONE) {
        $events_to_search = $tmp_events_to_search;
    }
    unset($tmp_events_to_search);

    $user_to_search                 = $events_to_search[0]->user;
    $date_start           = $events_to_search[0]->date_start;
    $date_end             = $events_to_search[0]->date_end;

    
    $date_start_timestamp  = substr($date_start, $p=0,  4) . "-" 
                           . substr($date_start, $p+=4, 2) . "-"
                           . substr($date_start, $p+=2, 2);
    $date_end_timestamp  =   substr($date_end,   $q=0,  4) . "-" 
                           . substr($date_end,   $q+=4, 2) . "-"
                           . substr($date_end,   $q+=2, 2); 

    if  (  strlen($date_start) == 8  && intval($date_start) !=0  )  
        $date_start_timestamp .= " 00:00:00   GMT";
    elseif (  strlen($date_start) == 14    && intval($date_start) !=0    )  
        $date_start_timestamp .= " " . substr($date_start, $p+=2, 2) . ":" 
                                     . substr($date_start, $p+=2, 2) . ":"
                                     . substr($date_start, $p+=2, 2);
    else
        return false;


    if (  strlen($date_end) == 8   &&  intval($date_end) !=0    )
        $date_end_timestamp   .=" 23:59:59   GMT";
    elseif (  strlen($date_end) ==14   &&  intval($date_end) !=0    )  
        $date_end_timestamp .= " "  . substr($date_end, $q+=2, 2) . ":" 
                                    . substr($date_end, $q+=2, 2) . ":"
                                    . substr($date_end, $q+=2, 2);
    else
        return false;


//    $events = $dbsogo->getEventsInDateRange($user,  $date_start_timestamp,  $date_end_timestamp);

    $database_location="127.0.0.1";
    $database_port="5433";
    $database_name="sogo";
    $database_user="sogo";

    $_passwords = array();
    $_passwords = getPasswords();
    $database_password = $_passwords["db_sogo_pw"];

    $db_gw = @pg_connect ("host=".$database_location." port=".$database_port." dbname=".$database_name." user=".$database_user." password=".$database_password);
    if ( ! $db_gw )  {
        $ret["result"]["resultcode"] = '-1';
        $ret["result"]["errorcode"][] = '-1 : Unable connect to SOGO database';

        ws_log("Unable connect to SOGO database\n");
        ws_log("Ret NOK: ".print_r ($ret, true)."\n");

        return $ret;
    }

    $query=" SELECT f.c_foldername ,
                   to_timestamp(c_startdate)::TIMESTAMP as c_startdate2, 
                   to_timestamp(e.c_enddate)::TIMESTAMP as c_enddate2, 
                   e.*     FROM    sogo_quick_appointment e   
               JOIN   sogo_folder_info f  ON (f.c_folder_id=e.c_folder_id) 
                 WHERE f.c_path2='$user_to_search'
                   and  to_timestamp(c_startdate)>='$date_start_timestamp'
                   and to_timestamp(c_enddate)<= '$date_end_timestamp';    ";
    
    ws_log("query: ".$query."\n");
 

    $r = @pg_exec($db_gw, $query);

    $result = pg_fetch_all($r);


    ws_log("\n\n RESULT =" . print_r($result,true));

    ws_log("\n\n RESULT [ 0 ]=" . print_r($result[0]['c_foldername'],true));



    $n_events = count((array)$result);
//    ws_log("Result : ".print_r($result, true)."\n");

    
    $ret["result"]["resultcode"] = 1;
    $ret["result"]["errorcode"][] = '';
    $ret["events"] = json_encode($result);
    $ret["n_events"] = $n_events;

    ws_log("Ret: ".print_r ($ret, true)."\n");


    
    return $ret;
    
}



/**
 * Update / Insert SOGO events on user calendars
 *
 * Returns an array with the result of the update / insert per calendar
 * 
 * Return -1 - Invalid Credentials
 * Return >1 - Success
 * 
 * @access public
 * @param array $events_array
 *   

$ics="BEGIN:VCALENDAR
PRODID:-//Inverse inc./SOGo 5.1.0//EN
VERSION:2.0
METHOD:PUBLISH
BEGIN:VTIMEZONE
TZID:Europe/London
LAST-MODIFIED:20210303T135712Z
X-LIC-LOCATION:Europe/London
BEGIN:DAYLIGHT
TZNAME:BST
TZOFFSETFROM:+0000
TZOFFSETTO:+0100
DTSTART:19700329T010000
RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=-1SU
END:DAYLIGHT
BEGIN:STANDARD
TZNAME:GMT
TZOFFSETFROM:+0100
TZOFFSETTO:+0000
DTSTART:19701025T020000
RRULE:FREQ=YEARLY;BYMONTH=10;BYDAY=-1SU
END:STANDARD
END:VTIMEZONE
BEGIN:VEVENT
UID:601-60D30F80-1-8D47A40
SUMMARY:consulta dentista
LOCATION:ermesinde
DESCRIPTION:consulta no dentista em ermesinde
CLASS:PUBLIC
TRANSP:OPAQUE
DTSTART;TZID=Europe/London:20210624T104500
DTEND;TZID=Europe/London:20210624T114500
CREATED:20210623T104130Z
DTSTAMP:20210623T104130Z
LAST-MODIFIED:20210623T104141Z
SEQUENCE:2
END:VEVENT
X-WR-CALNAME:Personal Calendar
END:VCALENDAR";

$calendars = array ();
$calendars[] = "personal";
$calendars[] = "Calendario1";
$calendars[] = "Calendario2";
$calendars[] = "Calendario3";

$events_array = array (
                  'login' => "pedro",
                  'ics' => $ics,
		  'calendars' => $calendars,
		  'sogo_admin_login' => "administrator", 
	          'sogo_admin_password' => base64_encode("R0laBill")
                );
 
$events_array = json_encode($events_array);

 * 
 * @return array
 */
function updateWSCalendar ($apiAccessLogin, $apiAccessPass, $events_updates)
{
    $authlogin = $apiAccessLogin;
    $authpassword = $apiAccessPass;
    
    if (is_file ("/tmp/systemws.log")) {
        error_log ("Function: updateWSCalendar\n", 3, "/tmp/systemws.log");
        error_log(print_r("API Access Login: ".$apiAccessLogin, true) . "\n", 3, "/tmp/systemws.log");
        error_log(print_r("API Access Password: ".$apiAccessPass, true) . "\n", 3, "/tmp/systemws.log");
        error_log("Variable events_updates encoded: ".print_r ($events_updates, true)."\n", 3, "/tmp/systemws.log");
    }

    $tmp_events_updates =  json_decode($events_updates) ;
    if (json_last_error() == JSON_ERROR_NONE) {
        $events_updates = $tmp_events_updates ;
    }
    unset($tmp_events_updates);
    
    if (is_file ("/tmp/systemws.log")) {
        error_log("Variable events_updates decoded: ".print_r ($events_updates, true)."\n", 3, "/tmp/systemws.log");
    }

    $authentication = authRequest ($authlogin, $authpassword);

    if ($authentication != 1) {
        $ret["result"]["resultcode"] = '-1';
        $ret["result"]["errorcode"][] = 1063;
        if (is_file ("/tmp/systemws.log")) {
            error_log("AuthRequest failed\n", 3, "/tmp/systemws.log");
            error_log("Ret NOK: ".print_r ($ret, true)."\n", 3, "/tmp/systemws.log");
        }
        return $ret;
    }
    
    $_passwords = array();
    $_passwords = getPasswords();
    $dbpass_sogo = $_passwords["db_sogo_pw"];
    
    $database_location="127.0.0.1";
    $database_port="5433";
    $database_name="sogo";
    $database_user="sogo";
    
    $_passwords = array();
    $_passwords = getPasswords();
    $database_password = $_passwords["db_sogo_pw"];
    
    $db_gw = @pg_connect ("host=".$database_location." port=".$database_port." dbname=".$database_name." user=".$database_user." password=".$database_password);
    if ( ! $db_gw )  {
        $ret["result"]["resultcode"] = '-1';
        $ret["result"]["errorcode"][] = '-1 : Unable connect to SOGO database';
        if (is_file ("/tmp/systemws.log")) {
            error_log("Unable connect to SOGO database\n", 3, "/tmp/systemws.log");
            error_log("Ret NOK: ".print_r ($ret, true)."\n", 3, "/tmp/systemws.log");
        }
        return $ret;
    }
    
    foreach($events_updates as $key => $value) {
        $$key = $value; //  Set variables:  $login, $ics, $calendars, $sogo_admin_login, $sogo_admin_password
    }
    
    
    //Ticket #16342  [IPBrick 7] webservice updateWSCalendar não está a atualizar eventos 
    //$file_path = "/tmp/tmp_event.ics";
    $file_path = tempnam("/tmp", "SOGO_webservice");
    exec("install -m 777 -o 0 -g 33 /dev/null $file_path", $result);
    
    $myfile = fopen($file_path, "w+");
    fputs($myfile, $ics);
    fclose($myfile);			
    
    
    $re = '/^UID:(.*)/m';
    preg_match_all($re, $ics, $matches, PREG_SET_ORDER, 0);

    $uid = $matches[0][1];
    
    if ( !isset($sogo_admin_login) || $sogo_admin_login=="" )
    {
        $ret["result"]["resultcode"] = "-5";
        $ret["result"]["errorcode"][] = '-5 : Missing sogo admin user login';
        if (is_file ("/tmp/systemws.log")) {
            error_log("Missing sogo admin user login\n", 3, "/tmp/systemws.log");
            error_log("Ret NOK: ".print_r ($ret, true)."\n", 3, "/tmp/systemws.log");
        }
        return $ret;
    }
    
    if ( !isset($sogo_admin_password) || $sogo_admin_password=="" )
    {
        $ret["result"]["resultcode"] = "-6";
        $ret["result"]["errorcode"][] = '-6 : Missing sogo admin user password';
        if (is_file ("/tmp/systemws.log")) {
            error_log("Missing sogo admin user password\n", 3, "/tmp/systemws.log");
            error_log("Ret NOK: ".print_r ($ret, true)."\n", 3, "/tmp/systemws.log");
        }
        return $ret;
    }
        
    if ( isset($calendars)  &&  ! empty($calendars[0]) )
    {
        for($i=0;$i<count((array)$calendars);$i++)
        {
            if($calendars[$i]!='personal') {
                    
                $query="select c_path4 from sogo_folder_info where c_path1='Users' and c_path2='$login' and c_foldername='$calendars[$i]';";
                if (is_file ("/tmp/systemws.log")) {
                    error_log("query: ".$query."\n", 3, "/tmp/systemws.log");
                }
                $result = @pg_exec($db_gw, $query);

                $r = pg_fetch_array($result);

                if ($r)
                {
                    $c_path4  = $r['c_path4'];
                    if (is_file ("/tmp/systemws.log")) {
                        error_log("c_path4: ".$c_path4."\n", 3, "/tmp/systemws.log");
                    }
                }
                else {
                    $ret["result"]["resultcode"] = "-2";
                    $ret["result"]["errorcode"][] = '-2 : Calendar '.$calendars[$i].' not found';
                    if (is_file ("/tmp/systemws.log")) {
                        error_log("Calendar '.$calendars[$i].' not found\n", 3, "/tmp/systemws.log");
                    }
                }
                exec("curl -k -u $sogo_admin_login:$sogo_admin_password -H \"Content-Type: text/calendar; charset=utf-8\" --upload-file $file_path http://localhost:20000/SOGo/dav/$login/Calendar/$c_path4/$uid.ics", $result);
                if (count((array)$result)>0) {
                    $ret["result"]["resultcode"] = "-3";
                    $ret["result"]["errorcode"][] = '-3 : Problems when creating event on calendar '.$calendars[$i];
                    if (is_file ("/tmp/systemws.log")) {
                        error_log("Problems when creating event on calendar ".$calendars[$i]."\n", 3, "/tmp/systemws.log");
                    }
                }
                else {
                    $ret["result"]["resultcode"] = "2";
                    $ret["result"]["errorcode"][] = '2 : New event created on calendar '.$calendars[$i];
                    if (is_file ("/tmp/systemws.log")) {
                        error_log("New event created on calendar ".$calendars[$i]."\n", 3, "/tmp/systemws.log");
                    }
                }
            }
            else {
                exec("curl -k -u $sogo_admin_login:$sogo_admin_password -H \"Content-Type: text/calendar; charset=utf-8\" --upload-file $file_path http://localhost:20000/SOGo/dav/$login/Calendar/personal/$uid.ics", $result);               
                if (count((array)$result)>0) {
                    $ret["result"]["resultcode"] = "-4";
                    $ret["result"]["errorcode"][] = '-4 : Problems when creating event on personal calendar';
                    if (is_file ("/tmp/systemws.log")) {
                        error_log("Problems when creating event on personal calendar\n", 3, "/tmp/systemws.log");
                    }
                }
                else {
                    $ret["result"]["resultcode"] = "1";
                    $ret["result"]["errorcode"][] = '1 : New event created on personal calendar';
                    if (is_file ("/tmp/systemws.log")) {
                        error_log("New event created on personal calendar\n", 3, "/tmp/systemws.log");
                    }
                }
            }
        }
    }

    unlink($file_path);

    if (is_file ("/tmp/systemws.log")) {
        error_log("Ret: ".print_r ($ret, true)."\n", 3, "/tmp/systemws.log");
    }
    
    return $ret;

}
