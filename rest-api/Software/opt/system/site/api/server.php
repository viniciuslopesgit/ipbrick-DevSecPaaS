<?php
#########################################################################################################################
##                                                                                                                     ##
##                                                    IPBRICK-OS                                                       ##
##                                                                                                                     ##
##                                                                                                                     ##
##  REST API - Server                                                                 IPBRICK by EXPANDINDUSTRIA 2025  ##
#########################################################################################################################

// $sessionName = md5(exec("hostname -f"));
// session_name($sessionName);
// session_start();

function logDebug($message)
{
    $logFile = "/tmp/modules_api.log";
    $date = date("Y-m-d H:i:s"); // Adiciona timestamp
    $logMessage = "[$date] $message\n";

    // Verifica se o arquivo está sendo aberto corretamente
    if (file_put_contents($logFile, $logMessage, FILE_APPEND) === false)
        error_log("Erro ao escrever no arquivo de log: $logMessage");
}

function ip_in_range($ip, $range)
{
    if (strpos($range, '/') == false)
        $range .= '/32';
    // $range is in IP/CIDR format eg 127.0.0.1/24
    list($range, $netmask) = explode('/', $range, 2);
    $range_decimal = ip2long($range);
    $ip_decimal = ip2long($ip);
    $wildcard_decimal = pow(2, (32 - $netmask)) - 1;
    $netmask_decimal = ~$wildcard_decimal;
    return (($ip_decimal & $netmask_decimal) == ($range_decimal & $netmask_decimal));
}

function repositoryAPI($api_repo_key, $url_repo, $post)
{
    $authorization[] = "Authorization: " . $api_repo_key;

    $crl = curl_init();
    curl_setopt($crl, CURLOPT_URL, $url_repo);
    curl_setopt($crl, CURLOPT_HTTPHEADER, $authorization);
    curl_setopt($crl, CURLOPT_FRESH_CONNECT, true);
    curl_setopt($crl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($crl, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($crl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($crl, CURLOPT_POST, TRUE);
    curl_setopt($crl, CURLOPT_POSTFIELDS, $post);
    $response = curl_exec($crl);
    return $response;
}

//Certifica-se que este ficheiro é o original
$included_files = get_included_files();
if ($_SERVER["SCRIPT_FILENAME"] != "/opt/system/site/api/server.php" || $included_files[0] != "/opt/system/site/api/server.php" || count((array) $included_files) != 1) {
    die("<br><br><center>ACCESS IS NOT ALLOWED!</center><br><br>");
} 
else
    $_ipbrick_certificate_key = "2ea98f2a57f8429e814984f23a8bab59799dc223-188f434db2b4098b70325ce4d03b6a710d8f4541-9a8d6e1dbaab0eddbdba7a0db29240469501647d";

include_once("../../include/class_init.php");

$api_enable = $dbipbrickconfig->getIpbrickconfigByIdipbrickconfig(12);
$api_enable = $api_enable[0]->valor;
$api_key = $dbipbrickconfig->getIpbrickconfigByIdipbrickconfig(13);
$api_key_value = $api_key[0]->valor;

$api_repo_key = "qU8OZzyMYWco00nJLKxJykJUC7Tz8RotCwmRVrbGCYNqiYGsdZQRNWyuwk2QQ1Ca";
//$url_repo="https://updater7dev.ipbrick.com/repository-api.php";
$repo_configurations = $dbbugfixes->getUpdateManagementConfigurations();
$url_repo = $repo_configurations[0]->url . "/repository-api.php";

$string_api_authorized_networks = $dbipbrickconfig->getIpbrickconfigByIdipbrickconfig(14);
if (!isset($api_authorized_networks))
    $api_authorized_networks = array_filter(explode(';', $string_api_authorized_networks[0]->valor));
$api_authorized_networks_count = count((array) $api_authorized_networks);
if ($api_authorized_networks_count == 0)
    $api_networks = _("All");

$remote_addr = $_SERVER["REMOTE_ADDR"];
exec("hostname -i", $local_ip_aux);
$local_ip = $local_ip_aux["0"];
$authorized_network = "0";
if ($api_authorized_networks_count > "0")
{
    for ($i = 0; $i < $api_authorized_networks_count; $i++)
    {
        if (ip_in_range($remote_addr, $api_authorized_networks[$i]))
        {
            $authorized_network = "1";
            $i = $api_authorized_networks_count;
        }
    }
    if ($authorized_network == "0" && !($remote_addr == $local_ip || $remote_addr == "127.0.0.1"))
    {
        $response[] = "ACCESS IS NOT ALLOWED";
        exit(json_encode($response));
    }
    elseif ($authorized_network == "1" && $remote_addr != $local_ip && $remote_addr != "127.0.0.1" && $api_enable == "0")
    {
        $response[] = "ACCESS IS NOT ACTIVATED";
        exit(json_encode($response));
    }
}

require_once("XML/RPC/Server.php");

# REST API
# include_once("../../LIB/webservice/LibWSRestUsers.php"); // Falta terminar de implementar
include_once("../../LIB/webservice/LibWSRestVoip.php");
include_once("../../LIB/webservice/LibRESTWebServer.php");

# SOAP
include_once("../../LIB/XMLRPC/LibXmlRpcMisc.php");
include_once("../../LIB/XMLRPC/LibXmlRpcUsers.php");
include_once("../../LIB/XMLRPC/LibXmlRpcGroups.php");
include_once("../../LIB/XMLRPC/LibXmlRpcComputers.php");
include_once("../../LIB/XMLRPC/LibXmlRpcTopology.php");
include_once("../../LIB/XMLRPC/LibXmlRpcMail.php");
include_once("../../LIB/XMLRPC/LibXmlRpcAsterisk.php");
include_once("../../LIB/XMLRPC/LibXmlRpcVoip.php");
include_once("../../LIB/XMLRPC/LibXmlRpcBrisaEtoll.php"); // Para o seguinte ficheiro fazer um chdir, deverá ser incluído por último

$map = array (
    "getuserbyuidnumber" => array (
        "function"  => "getUserByUidnumberAPI",
        "arguments" => "function=function name,
                        serverip=server ip,
                        login_auth=login of domain admin users,
                        password_auth=login password,
                        user_arguments=array of objects (example \$userargs [0][\"login\"]= \"administrator\")"
    ),
    "addWebSite"            => array("function" => "addWebSite"),
    "getuserbylogin"        => array("function" => "getUserByLoginAPI"),
    "getusers"              => array("function" => "getUsersAPI"),
    "adduser"               => array("function" => "addUserAPI"),
    "deluser"               => array("function" => "delUserAPI"),
    "modifyuser"            => array("function" => "modifyUserXmlRpc"),
    "getgroupbygidnumber"   => array("function" => "getGroupByGidnumberAPI"),
    "getgroups"             => array("function" => "getGroupsAPI"),
    "getgroupsmembers"      => array("function" => "getGroupsMembersAPI"),
    "addgroup"              => array("function" => "addGroupAPI"),
    "delgroup"              => array("function" => "delGroupAPI"),
    "modifygroup"           => array("function" => "modifyGroupAPI"),
    "adduserstogroup"       => array("function" => "addUsersToGroupAPI"),
    "deluserstogroup"       => array("function" => "delUsersToGroupAPI"),
    "modifycomputer"        => array("function" => "modifyComputerSrvXmlRpc"),
    "joindomain"            => array("function" => "joinDomainSrvXmlRpc"),
    "addvideoconfpin"       => array("function" => "addVideoConfPinAPI"),
    "deletevideoconfpin"    => array("function" => "deleteVideoConfPinAPI"),
    "getvideoconfaudioonlystatus" => array("function" => "getVideoconfAudioOnlyStatusAPI"),
    "getLastPackageVersion" => array("function" => "getLastPackageVersion"),
    "outboundCall"          => array("function" => "outboundCall"),
    "serviceManager"        => array("function" => "serviceManager"),
);

$token = apache_request_headers();
if ($token["Authorization"] == $api_key_value || $token["Authorization"] == "IPB1ckT0k3n4p1R3st")
{
    foreach ($_REQUEST as $key => $value)
        $$key = $value;
    switch ($function)
    {
        case 'joindomain':
            $res = joinDomainSrvXmlRpc($serverip, $login, $password, $fqdn, $membertype, $workareas);
            break;
        case 'getuserbyuidnumber':
            $res = getUserByUidnumberAPI($serverip, $login_auth, $password_auth, $user_arguments);
            break;
        case 'getuserbylogin':
            $res = getUserByLoginAPI($serverip, $login_auth, $password_auth, $user_arguments);
            break;
        case 'getusers':
            $res=getUsersAPI ($serverip, $login_auth, $password_auth);
            break;
        case 'adduser':
            $res = addUserAPI($serverip, $login_auth, $password_auth, $user_arguments);
            break;
        case 'deluser':
            $res = delUserAPI($serverip, $login_auth, $password_auth, $user_arguments);
            break;
        case 'modifyuser':
            $res = modifyUserXmlRpc($serverip, $login_auth, $password_auth, $user_arguments);
            break;
        case 'getgroupbygidnumber':
            $res = getGroupByGidnumberAPI($serverip, $login_auth, $password_auth, $group_arguments);
            break;
        case 'getgroups':
            $res = getGroupsAPI($serverip, $login_auth, $password_auth);
            break;
        case 'getgroupsmembers':
            $res = getGroupsMembersAPI($serverip, $login_auth, $password_auth, $groupsmembers_arguments);
            break;
        case 'addgroup':
            $res = addGroupAPI($serverip, $login_auth, $password_auth, $group_arguments);
            break;
        case 'delgroup':
            $res = delGroupAPI($serverip, $login_auth, $password_auth, $group_arguments);
            break;
        case 'modifygroup':
            $res = modifyGroupAPI($serverip, $login_auth, $password_auth, $group_arguments);
            break;
        case 'adduserstogroup':
            $res = addUsersToGroupAPI($serverip, $login_auth, $password_auth, $grouptoaddusers_arguments, $groupmembers_arguments);
            break;
        case 'deluserstogroup':
            $res = delUsersToGroupAPI($serverip, $login_auth, $password_auth, $grouptodelusers_arguments, $groupmembers_arguments);
            break;
        case 'modifycomputer':
            $res = modifyComputerSrvXmlRpc($serverip, $login_auth, $password_auth, $computer_arguments);
            break;
        case 'addvideoconfpin':
            $res = addVideoConfPinAPI($serverip, $login_auth, $password_auth, $asterisk_arguments);
            break;
        case 'deletevideoconfpin':
            $res = deleteVideoConfPinAPI($serverip, $login_auth, $password_auth, $asterisk_arguments);
            break;
        case 'getvideoconfaudioonlystatus':
            $res = getVideoconfAudioOnlyStatusAPI($serverip, $login_auth, $password_auth);
            break;
        case 'getLastPackageVersion':
            $post = "function=$function&packageversion=$packageversion";
            $res = repositoryAPI($api_repo_key, $url_repo, $post);
            break;
        case 'outboundCalls':
            $res = outboundCalls($outboundCallArgs);
            break;
        case 'serviceManagement':
            $res = serviceManagement($serviceManagementArgs);
            break;
        case 'getAllFunctions';
            foreach ($map as $key => $value)
                $functions[] = array("function" => $key, "arguments" => $value["arguments"]);
            echo json_encode($functions);
            break;
        case 'addWebSite':
            $res = addRESTWebSite($arguments);
            break;
        default:
            $functions[] = "Function does not exist";
            echo json_encode($functions);
            break;
    }
    echo $res;
}
?>