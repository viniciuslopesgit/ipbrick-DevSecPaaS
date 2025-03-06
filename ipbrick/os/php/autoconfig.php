#!/usr/bin/php -q
<?php
/**
 * <Modify cofigurations in cloud scenarios>
 *
 * PHP version 5
 *
 * LICENSE: IPBRICK License
 *  This code is property of IPBRICK International.
 *
 *
 * @category   System  
 * @package    ipbrick
 * @author     IPBRICK <support@ipbrick.com>
 * @copyright  2000-2015 IPBRICK International
 * @license    IPBRICK License
 * @version    CVS: $Id: autoconfig.php,v 1.9 2021-09-08 09:53:36 amarques Exp $
 * @link       http://www.ipbrick.com
 * 
 */

//TODO: put the functions in LIB folder and use that function when change the domain and IP in the Web Interface
/*PARAMS:
--runAs=autoconfig/monitor/onlyapply (default autoconfig)
--deletesessao=1/0 (default 0)
*/
$params['runAs'] = "";
$params['root_password'] = "";

if(count ($argv) > 1) {
    foreach ($argv as $arg) {
        $e=explode("=",$arg);
        if(count($e)==2) {
            $params[str_replace("--", "", $e[0])]=$e[1];
        }
    }    
}

if ($params['runAs'] == "autoconfig") {
    if (!is_file ("/tmp/.autoconf_running.lock")) {
        error_log (date("y-m-d/H:i:s",time())." - 'AUTO CONFIGURATION - Starts the customization script' \n", 3, "/opt/system/log/system.log");
        exec("touch /tmp/.autoconf_running.lock");
    } else {
        error_log (date("y-m-d/H:i:s",time())." - 'AUTO CONFIGURATION - Already running' \n", 3, "/opt/system/log/system.log");
        exit (0);
    }
}

//check the authenticity of the script
$included_files = get_included_files ();
if ($_SERVER["SCRIPT_FILENAME"] != "/opt/system/scripts/autoconfig.php" || $included_files[0] != "/opt/system/scripts/autoconfig.php" || count ($included_files) != 1) {
    echo "ACCESS IS NOT ALLOWED!\r\n";
    exit (1);
} else {    
    $_ipbrick_certificate_key = "2ea98f2a57f8429e814984f23a8bab59799dc223-188f434db2b4098b70325ce4d03b6a710d8f4541-9a8d6e1dbaab0eddbdba7a0db29240469501647d";
}

include_once ("/opt/system/site_xxx.php");
include_once ("/opt/system/LIB/LibMisc.php");

//There are many changes, so Include IPBrick code to simplify
include_once ("/opt/system/include/class_init.php");

//////////////////////////////////////////////////////////////////////////////////
//                               Functions
//////////////////////////////////////////////////////////////////////////////////

function mask2cidr($mask){  
     $long = ip2long($mask);  
     $base = ip2long('255.255.255.255');  
     return 32-log(($long ^ $base)+1,2);       
}

function CIDRtoMask($int) {
    return long2ip(-1 << (32 - (int)$int));
}

function cidr2broadcast($network, $cidr)
{
  $broadcast = long2ip(ip2long($network) + pow(2, (32 - $cidr)) - 1);
  return $broadcast;
}

function changeGateway ($system_canonical, $interface, $gateway, &$error) 
{
  $f_ip = explode (".", $gateway);
    
  $url="https://$system_canonical/index.php?Nome=admin&Password=123456&OK=OK";
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); //Important for invalid certs
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); //Mandatory
  //curl_setopt($ch, CURLOPT_R:q:qETURNTRANSFER, TRUE); //Verbose
  curl_setopt($ch, CURLOPT_COOKIEJAR, "cookiefile"); //Mandatory
  curl_setopt($ch, CURLOPT_URL, $url); 
  $result1 = curl_exec($ch);
  $postfields = "&f_interface=".$interface.
                "&f_ipgateway_1=$f_ip[0]&f_ipgateway_2=$f_ip[1]&f_ipgateway_3=$f_ip[2]&f_ipgateway_4=".$f_ip[3];

  $url2="https://$system_canonical/corpo.php?pagina=interface_gw_alterar_altera$postfields";
  curl_setopt($ch, CURLOPT_URL, $url2);
  $content = curl_exec ($ch);
  curl_close ($ch);
}


function changeDomainName ($system_canonical,&$error)
{
  include_once ("/root/execCommand.cfg");
  $url="https://$system_canonical/index.php?Nome=admin&Password=123456&OK=OK";
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); //Important for invalid certs
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); //Mandatory
  //curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); //Verbose
  curl_setopt($ch, CURLOPT_COOKIEJAR, "cookiefile"); //Mandatory
  curl_setopt($ch, CURLOPT_URL, $url); 
  $result1 = curl_exec($ch); 
  $url2="https://$system_canonical/corpo.php?pagina=servidor_alterar_altera&f_nome=ipbrick&f_dominio=$domain";
  curl_setopt($ch, CURLOPT_URL, $url2);
  $content = curl_exec ($ch);
  curl_close ($ch);
}


function changeInterface ($system_canonical, $f_interface, $system_ip, $f_mask, $system_network, $system_bcast, $f_name, $f_encap, $f_type, $f_mode, &$error)
{
  $f_ip = explode (".", $system_ip);
  $f_nw = explode (".", $system_network);
  $f_bc = explode (".", $system_bcast);
  
  $url="https://$system_canonical/index.php?Nome=admin&Password=123456&OK=OK";
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); //Important for invalid certs
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); //Mandatory
  //curl_setopt($ch, CURLOPT_R:q:qETURNTRANSFER, TRUE); //Verbose
  curl_setopt($ch, CURLOPT_COOKIEJAR, "cookiefile"); //Mandatory
  curl_setopt($ch, CURLOPT_URL, $url); 
  $result1 = curl_exec($ch);
  $postfields = "&f_interface=".$f_interface.
                "&f_ip_1=$f_ip[0]&f_ip_2=$f_ip[1]&f_ip_3=$f_ip[2]&f_ip_4=".$f_ip[3].
                "&f_mascara=".$f_mask.
                "&f_iprede_1=$f_nw[0]&f_iprede_2=$f_nw[1]&f_iprede_3=$f_nw[2]&f_iprede_4=".$f_nw[3].
                "&f_ipbroadcast_1=$f_bc[0]&f_ipbroadcast_2=$f_bc[1]&f_ipbroadcast_3=$f_bc[2]&f_ipbroadcast_4=".$f_bc[3].
                "&f_modo=".$f_mode."&encap=".$f_encap."&f_nome=".$f_name."&f_tipo=".$f_type;

  $url2="https://$system_canonical/corpo.php?pagina=interface_alterar_altera$postfields";
  curl_setopt($ch, CURLOPT_URL, $url2);
  $content = curl_exec ($ch);
  curl_close ($ch);
}

function changePasswords ($root_password)
{
    global $dbusersystem,$dbvalida;
    global $_dbhost,$_dbport,$_pg_backsys_dbpass_5433, $_pg_backsys_dbuser_5433;
    
    $dbusersystem->updatePasswdUsersystem ('1', $root_password);
    $dbusersystem->updatePasswdUsersystem ('2', $root_password);
    $dbusersystem->updatePasswdUsersystem ('3', $root_password);
    $dbusersystem->updatePasswdUsersystem ('4', $root_password);
    $dbusersystem->updatePasswdUsersystem ('6', $root_password);
    $dbusersystem->updatePasswdUsersystem ('7', $root_password);
    $dbvalida->updateValida ('admin', $root_password, 0);
    //CAFE
    $cafebd = new ligabd ($_dbhost, $_dbport, "cafe", $_pg_backsys_dbpass_5433, $_pg_backsys_dbuser_5433);
    if ($cafebd->conn != -1) {
        $query = "UPDATE \"user\" SET password='".md5($root_password)."' WHERE username='admin';";
        @pg_exec($cafebd->conn, $query);
        $cafebd->closedb();
    }
    //Administrator
    $userargs['usernumber'] = "10000";
    $userargs['password'] = $root_password;
    modifyUser ($userargs, $error);    
    return 1;
}


function remove_provisioning_page ()
{
  exec ("rm /opt/system/site/portal.html"); //Exist only in first version of lunacloud-setup
  exec ("rm /opt/system/site/portal.php");
  exec ("rm -rf /opt/system/site/portalfiles");
  
  $checksum = "rm -f /tmp/sha1sum
  find /opt/system/ -group 33 -iregex '.*\.php$' -exec sha1sum -b {} \; | grep -v erro_acesso1.php | grep -v erro_acesso2.php | grep -v '/opt/system/gerados' | grep -v '/opt/system/backupDB' | grep -v '/opt/system/backupSYS' | grep -v '/opt/system/ReleaseNotes' >> /tmp/sha1sum
  find /opt/system/ -group 33 -iregex '.*\.phpclass$' -exec sha1sum -b {} \; >> /tmp/sha1sum
  sha1sum -b /opt/system/site.conf >> /tmp/sha1sum
  sha1sum -b /opt/system/include/repor_base/defaultconfig.zip >> /tmp/sha1sum
  zip -P SHA1SUMIPBRICKCHECK /opt/system/application.dat /tmp/sha1sum
  rm -f /tmp/sha1sum
  chmod 750 /opt/system/application.dat
  chown 0.33 /opt/system/application.dat";
  exec ($checksum);
  return 1;
}

function change_DNS_Machines ($system_canonical, $f_idzona,$f_iddns_in_a,$f_nome)
{
  $mac= exec ("ifconfig eth1 | grep ether | awk {'print $2'}");
  $url="http://checkip.amazonaws.com";
  $ch = curl_init();
  // define options
  $optArray = array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true
  );
  // apply those options
  curl_setopt_array($ch, $optArray);
  // execute request and get response
  $system_ip = trim(curl_exec($ch));

  $f_ip = explode (".", $system_ip);

  $url="https://$system_canonical/index.php?Nome=admin&Password=123456&OK=OK";
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); //Important for invalid certs
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); //Mandatory
  //curl_setopt($ch, CURLOPT_R:q:qETURNTRANSFER, TRUE); //Verbose
  curl_setopt($ch, CURLOPT_COOKIEJAR, "cookiefile"); //Mandatory
  curl_setopt($ch, CURLOPT_URL, $url);
  $result1 = curl_exec($ch);

  $postfields = "&f_idzona=$f_idzona&f_iddns_in_a=$f_iddns_in_a&f_nome=$f_nome&f_ip1=$f_ip[0]&f_ip2=$f_ip[1]&f_ip3=$f_ip[2]&f_ip4=$f_ip[3]&f_accao=Modify";
  $url2="https://$system_canonical/corpo.php?pagina=dns_in_a_alterar_altera$postfields";
  curl_setopt($ch, CURLOPT_URL, $url2);
  $content = curl_exec ($ch);
  curl_close ($ch);
}
 
function change_Videoconference_Settings ($system_canonical, $private_ip)
{
 /* $mac= exec ("ifconfig eth1 | grep ether | awk {'print $2'}");
  $url="http://169.254.169.254/latest/meta-data/network/interfaces/macs/".$mac."/public-ipv4s";
  $ch = curl_init();
  // define options
  $optArray = array(
      CURLOPT_URL => $url,
      CURLOPT_RETURNTRANSFER => true
  );
  // apply those options
  curl_setopt_array($ch, $optArray);
  // execute request and get response
  $system_ip = curl_exec($ch);*/

  $system_ip = exec ("dig +short myip.opendns.com @resolver1.opendns.com");

  $f_ip = explode (".", $system_ip);

  $url="https://$system_canonical/index.php?Nome=admin&Password=123456&OK=OK";
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); //Important for invalid certs
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); //Mandatory
  //curl_setopt($ch, CURLOPT_R:q:qETURNTRANSFER, TRUE); //Verbose
  curl_setopt($ch, CURLOPT_COOKIEJAR, "cookiefile"); //Mandatory
  curl_setopt($ch, CURLOPT_URL, $url);
  $result1 = curl_exec($ch);

  $postfields = "&op=1&nat_local_address=$private_ip&nat_public_address=$system_ip";
  $url2="https://$system_canonical/corpo.php?pagina=videoconf_settings_mod_act$postfields";
  curl_setopt($ch, CURLOPT_URL, $url2);
  $content = curl_exec ($ch);
  curl_close ($ch);
}

function create_Local_Name_Resolution ($system_canonical,$system_ip)
{
  include ("/root/execCommand.cfg");
  $f_ip = explode (".", $system_ip);
  $url="https://$system_canonical/index.php?Nome=admin&Password=123456&OK=OK";
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); //Important for invalid certs
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); //Mandatory
  //curl_setopt($ch, CURLOPT_R:q:qETURNTRANSFER, TRUE); //Verbose
  curl_setopt($ch, CURLOPT_COOKIEJAR, "cookiefile"); //Mandatory
  curl_setopt($ch, CURLOPT_URL, $url);
  $result1 = curl_exec($ch);

  //Ticket #9 - Alterar a função "create_Local_Name_Resolution" para não adicionar novamente a entrada para "ipbrick.domain.com" no /etc/hosts
  //Ticket #10 - Adicionar entradas de gestão (docker e devops) nas hosts (/etc/hosts)
  $postfields="&f_host0=cafe.$domain&f_ip1_0=$f_ip[0]&f_ip2_0=$f_ip[1]&f_ip3_0=$f_ip[2]&f_ip4_0=$f_ip[3]&f_idhost0=".
  "&f_host1=devops.$domain&f_ip1_1=$f_ip[0]&f_ip2_1=$f_ip[1]&f_ip3_1=$f_ip[2]&f_ip4_1=$f_ip[3]&f_idhost1=".
  "&f_host2=docker.$domain&f_ip1_2=$f_ip[0]&f_ip2_2=$f_ip[1]&f_ip3_2=$f_ip[2]&f_ip4_2=$f_ip[3]&f_idhost2=".
  "&f_host3=im.$domain&f_ip1_3=$f_ip[0]&f_ip2_3=$f_ip[1]&f_ip3_3=$f_ip[2]&f_ip4_3=$f_ip[3]&f_idhost3=".
  "&f_host4=ucoip.$domain&f_ip1_4=$f_ip[0]&f_ip2_4=$f_ip[1]&f_ip3_4=$f_ip[2]&f_ip4_4=$f_ip[3]&f_idhost4=".
  "&f_host5=voip.$domain&f_ip1_5=$f_ip[0]&f_ip2_5=$f_ip[1]&f_ip3_5=$f_ip[2]&f_ip4_5=$f_ip[3]&f_idhost5=".
  "&f_host6=webrtc.$domain&f_ip1_6=$f_ip[0]&f_ip2_6=$f_ip[1]&f_ip3_6=$f_ip[2]&f_ip4_6=$f_ip[3]&f_idhost6=&alterar=Modify";


  $url2="https://$system_canonical/corpo.php?pagina=dns_local_names_modify_acc&reloadpag=1&total_dns_hosts_number=6&total_dns_hosts_number_add=0&deldnshost=&iddeldnshost=-1$postfields";
  curl_setopt($ch, CURLOPT_URL, $url2);
  $content = curl_exec ($ch);
  curl_close ($ch);
}

//Ticket #6 Adicionar autorização para resolução de DNS para as redes dos docker - 172.0.0.0/8
function changeDnsRecursion ()
{
    global $dbnamed_conf;
    $dbnamed_conf->insertDnsRecursion ('172.0.0.0', '8');
    return 1;
}

function azure_curl_query ($query)
{
//echo $query;
  $ch = curl_init();
  // define options
  $optArray = array(
      CURLOPT_URL => $query,
      CURLOPT_RETURNTRANSFER => true
  );
  // apply those options
  curl_setopt_array($ch, $optArray);
  $result1 = curl_exec($ch);
  return $result1;
}

////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////

#######################################################################################
#       Change database Configurations - Auto Configuration
#######################################################################################
//GET IP AND MACHINE NAME FROM SYSTEM
//To test only
//exec ("ifconfig eth0 10.10.10.199/24");

$system_eth0_ip = exec("/sbin/ip addr  | grep \"scope global eth0\"  | awk {'print $2'} | cut -d/ -f1");
$system_eth0_mask_cidr = exec ("/sbin/ip addr  | grep \"scope global eth0\" | awk {'print $2'} | cut -d/ -f2");
$system_eth0_mask=CIDRtoMask($system_eth0_mask_cidr);
$system_eth0_bcast = exec ("/sbin/ip addr  | grep \"scope global eth0\" | awk {'print $4'}");
//$system_eth1_ip = exec("/sbin/ip addr  | grep \"scope global dynamic eth1\"  | awk {'print $2'} | cut -d/ -f1");
$system_eth1_ip = exec("/sbin/ip addr  | grep \"scope global eth1\"  | awk {'print $2'} | cut -d/ -f1");
//$system_eth1_mask_cidr = exec ("/sbin/ip addr  | grep \"scope global dynamic eth1\" | awk {'print $2'} | cut -d/ -f2");
$system_eth1_mask_cidr = exec ("/sbin/ip addr  | grep \"scope global eth1\" | awk {'print $2'} | cut -d/ -f2");
$system_eth1_mask=CIDRtoMask($system_eth1_mask_cidr);
//$system_eth1_bcast = exec ("/sbin/ip addr  | grep \"scope global dynamic eth1\" | awk {'print $4'}");
$system_eth1_bcast = exec ("/sbin/ip addr  | grep \"scope global eth1\" | awk {'print $4'}");
$publicIP_DNAT = exec ("dig +short myip.opendns.com @resolver1.opendns.com");
$publicIP_DNAT = trim($publicIP_DNAT);

$system_hostname  = exec ("hostname -s");
$system_domain    = exec ("hostname -d");
$system_canonical = exec ("hostname -f");

$system_gateway = exec("/sbin/ip route | awk '/default/ { print $3 }'");
$gw_interface = "eth1";

if ($system_eth1_ip != '') {
    $publicIP = $system_eth1_ip;
} else {
    $publicIP = $system_eth0_ip;
}

//default IP address of template
$db_eth0 = "192.168.69.199";
$db_eth1 = "10.0.0.253";
$db_eth0_interface = "0";
$db_eth1_interface = "1";
$db_eth0_nome = "eth0";
$db_eth1_nome = "eth1";
$db_eth0_encap = $db_eth1_encap = "1";
$db_eth0_tipo = "1";
$db_eth1_tipo = "2";
$db_eth0_modo = $db_eth1_modo = "static";

if ($params['runAs'] == "networkconfig") {
  error_log (date("y-m-d/H:i:s",time())." - 'NETWORK CONFIGURATION - Network changes detected' \n", 3, "/opt/system/log/system.log");

//UPDATE VIRTUALHOST (APACHE) and HOSTS - if there is network changes
    exec ("/etc/init.d/packetfilter stop");//Disable firewall
    sleep (2);
    error_log (date("y-m-d/H:i:s",time())." - 'NETWORK CONFIGURATION - Modify system confs' \n", 3, "/opt/system/log/system.log");
    exec ("sed -i -e 's/".$db_eth0."/".$system_eth0_ip."/g' /etc/apache2/sites-enabled/200-1-*  2> /dev/null");
    if ($system_eth1_ip != '') {
        exec ("sed -i -e 's/ipbrick/".$system_eth1_ip."/g' /etc/apache2/sites-enabled/200-1-*  2> /dev/null");
    }
    //exec ("/etc/init.d/apache2 reload");

//TO use CURL
    error_log (date("y-m-d/H:i:s",time())." - 'NETWORK CONFIGURATION - Modify hostname (".$system_hostname.")' \n", 3, "/opt/system/log/system.log");
    exec("echo \"$system_hostname\" > /etc/hostname 2> /dev/null");
    $hosts_txt = "127.0.0.1       localhost $system_canonical
$system_eth0_ip      $system_hostname
    ";
    exec ("echo \"$hosts_txt\" > /etc/hosts  2> /dev/null");
    exec ("/etc/init.d/hostname.sh");
    sleep (5);
    error_log (date("y-m-d/H:i:s",time())." - 'NETWORK CONFIGURATION - Modify database confs' \n", 3, "/opt/system/log/system.log");
    $dbsessao->deletesessao ();
    $eth0_ip_coverted = ip2long($system_eth0_ip);
    $eth0_mask_converted = ip2long($system_eth0_mask);
    $eth0_network = long2ip($eth0_ip_coverted & $eth0_mask_converted);
    error_log (date("y-m-d/H:i:s",time())." - 'NETWORK CONFIGURATION - Change interface' \n", 3, "/opt/system/log/system.log");
    $result2=changeInterface($system_canonical,$db_eth0_interface,$system_eth0_ip,mask2cidr($system_eth0_mask),$eth0_network,$system_eth0_bcast,$db_eth0_nome,$db_eth0_encap,$db_eth0_tipo,$db_eth0_modo,$error2);
    $dbsessao->deletesessao ();
    if ($system_eth1_ip != '') {
        $eth1_ip_coverted = ip2long($system_eth1_ip);
        $eth1_mask_converted = ip2long($system_eth1_mask);
        $eth1_network = long2ip($eth1_ip_coverted & $eth1_mask_converted);
        $result3 = changeInterface($system_canonical,$db_eth1_interface,$system_eth1_ip,mask2cidr($system_eth1_mask),$eth1_network,$system_eth1_bcast,$db_eth1_nome,$db_eth1_encap,$db_eth1_tipo,$db_eth1_modo,$error3);
        $dbsessao->deletesessao ();
    } else $result3 = 1;
    if ($gw_interface == 'eth0') {
        $interface = 0;
    } else {
        $interface = 1;
    }
    error_log (date("y-m-d/H:i:s",time())." - 'NETWORK CONFIGURATION - Change gateway' \n", 3, "/opt/system/log/system.log");
    $result4 = changeGateway ($system_canonical,$interface,$system_gateway,$error4);
    $dbsessao->deletesessao ();
   $hosts_txt = "127.0.0.1       localhost
$system_eth0_ip      $system_canonical $system_hostname
    ";
    exec ("echo \"$hosts_txt\" > /etc/hosts  2> /dev/null");
    exec ("/etc/init.d/hostname.sh");
    sleep (5);
//Finish use CURL  

}

if ($params['runAs'] == "autoconfig") {
  error_log (date("y-m-d/H:i:s",time())." - 'AUTO CONFIGURATION - Network changes detected' \n", 3, "/opt/system/log/system.log");

//UPDATE VIRTUALHOST (APACHE) and HOSTS - if there is network changes
    exec ("/etc/init.d/packetfilter stop");//Disable firewall
    sleep (2);
    error_log (date("y-m-d/H:i:s",time())." - 'AUTO CONFIGURATION - Modify system confs' \n", 3, "/opt/system/log/system.log");
    exec ("sed -i -e 's/".$db_eth0."/".$system_eth0_ip."/g' /etc/apache2/sites-enabled/200-1-*  2> /dev/null");
    if ($system_eth1_ip != '') {
        exec ("sed -i -e 's/ipbrick/".$system_eth1_ip."/g' /etc/apache2/sites-enabled/200-1-*  2> /dev/null");
    }
    exec ("/etc/init.d/apache2 reload");
    error_log (date("y-m-d/H:i:s",time())." - 'AUTO CONFIGURATION - Modify hostname (".$system_hostname.")' \n", 3, "/opt/system/log/system.log");
    exec("echo \"$system_hostname\" > /etc/hostname 2> /dev/null");
    $hosts_txt = "127.0.0.1       localhost $system_canonical
$system_eth0_ip      $system_hostname
    ";
    exec ("echo \"$hosts_txt\" > /etc/hosts  2> /dev/null");
    exec ("/etc/init.d/hostname.sh");
    sleep (5);

    error_log (date("y-m-d/H:i:s",time())." - 'AUTO CONFIGURATION - Modify database confs' \n", 3, "/opt/system/log/system.log");
    $dbsessao->deletesessao ();
    error_log (date("y-m-d/H:i:s",time())." - 'AUTO CONFIGURATION - Change name' \n", 3, "/opt/system/log/system.log");
    $result1 = changeDomainName ($system_canonical, $error1);
    $dbsessao->deletesessao ();
    $eth0_ip_coverted = ip2long($system_eth0_ip);
    $eth0_mask_converted = ip2long($system_eth0_mask);
    $eth0_network = long2ip($eth0_ip_coverted & $eth0_mask_converted);
    error_log (date("y-m-d/H:i:s",time())." - 'AUTO CONFIGURATION - Change interface' \n", 3, "/opt/system/log/system.log");
    $result2=changeInterface($system_canonical,$db_eth0_interface,$system_eth0_ip,mask2cidr($system_eth0_mask),$eth0_network,$system_eth0_bcast,$db_eth0_nome,$db_eth0_encap,$db_eth0_tipo,$db_eth0_modo,$error2);
    $dbsessao->deletesessao ();
    if ($system_eth1_ip != '') {
        $eth1_ip_coverted = ip2long($system_eth1_ip);
        $eth1_mask_converted = ip2long($system_eth1_mask);
        $eth1_network = long2ip($eth1_ip_coverted & $eth1_mask_converted);
        $result3 = changeInterface($system_canonical,$db_eth1_interface,$system_eth1_ip,mask2cidr($system_eth1_mask),$eth1_network,$system_eth1_bcast,$db_eth1_nome,$db_eth1_encap,$db_eth1_tipo,$db_eth1_modo,$error3);
        $dbsessao->deletesessao ();
    } else $result3 = 1;
    
    if ($gw_interface == 'eth0') {
        $interface = 0;
    } else {
        $interface = 1;
    }
/*    
    #configure DNAT
    $systemconfbd = new ligabd ($_dbhost, $_dbport, "systemconf", $_pg_backsys_dbpass_5433, $_pg_backsys_dbuser_5433);
    if ($systemconfbd->conn != -1) {
        $query = "UPDATE voip_options SET estado ='t', extra='0' where opcao in ('PUBLIC_IP_TYPE');";
        @pg_exec($systemconfbd->conn, $query);
        $query = "UPDATE voip_options SET estado ='t', extra='".$publicIP_DNAT."' where opcao in ('PUBLIC_IP_VALUE','DNAT');";
        @pg_exec($systemconfbd->conn, $query);
        $query = "UPDATE alteracao SET alterado ='t' where servico in ('VOIPOPTIONS','VOIP_DNAT','VOIP_SIPPROXY');";
        @pg_exec($systemconfbd->conn, $query);
        $systemconfbd->closedb();
    }

*/
    error_log (date("y-m-d/H:i:s",time())." - 'AUTO CONFIGURATION - Change gateway' \n", 3, "/opt/system/log/system.log");
    $result4 = changeGateway ($system_canonical,$interface,$system_gateway,$error4);
    $dbsessao->deletesessao ();

    error_log (date("y-m-d/H:i:s",time())." - 'AUTO CONFIGURATION - Change DNS Machines' \n", 3, "/opt/system/log/system.log");

    $result4=change_DNS_Machines($system_canonical,"1","5","cafe");
    $dbsessao->deletesessao ();
    $result5=change_DNS_Machines($system_canonical,"1","3","im");
    $dbsessao->deletesessao ();
    $result6=change_DNS_Machines($system_canonical,"1","1","ipbrick");
    $dbsessao->deletesessao ();
    $result7=change_DNS_Machines($system_canonical,"1","4","ucoip");
    $dbsessao->deletesessao ();
    $result8=change_DNS_Machines($system_canonical,"1","2","voip");
    $dbsessao->deletesessao ();
    $result9=change_DNS_Machines($system_canonical,"1","6","webrtc");
    $dbsessao->deletesessao ();
    error_log (date("y-m-d/H:i:s",time())." - 'AUTO CONFIGURATION - Create Local Name Resolution' \n", 3, "/opt/system/log/system.log");
    $result10=create_Local_Name_Resolution ($system_canonical,$system_eth0_ip);
    $dbsessao->deletesessao ();
    error_log (date("y-m-d/H:i:s",time())." - 'AUTO CONFIGURATION - Change Videoconference Settings' \n", 3, "/opt/system/log/system.log");
    $result11=change_Videoconference_Settings ($system_canonical,$system_eth1_ip);
    //Ticket #6 Adicionar autorização para resolução de DNS para as redes dos docker - 172.0.0.0/8
    error_log (date("y-m-d/H:i:s",time())." - 'AUTO CONFIGURATION - Change DnsRecursion' \n", 3, "/opt/system/log/system.log");
    $result12 = changeDnsRecursion ();
    //exit (0);

    include ("/root/execCommand.cfg");
    $hosts_txt = "127.0.0.1       localhost
$system_eth0_ip      ipbrick.$domain ipbrick
        ";
        exec ("echo \"$hosts_txt\" > /etc/hosts  2> /dev/null");
        exec ("/etc/init.d/hostname.sh");
        sleep (5);


    //exec ("sed -i -e 's/exit\ 0/php\ \/opt\/system\/scripts\/autoconfig.php\ runAs=\"creatednsentry\"/g'  /etc/rc.local");
    //exec ("echo \"exit 0\" >> /etc/rc.local");
    exec ("install -v -m 750 -o root -g root  /opt/system/scripts/02_prov.sh  /etc/boot.d/02_prov");

    error_log (date("y-m-d/H:i:s",time())." - 'AUTO CONFIGURATION - Change destination address to send configuration' \n", 3, "/opt/system/log/system.log");
    $ipbrick_props = $dbipbrickemail->getIPBrickEmail();
    $system_email = "administrator@".$domain;
    $user_email = $mail;
    $dbipbrickemail->updateIPBrickEmail ($system_email, $ipbrick_props[0]->assunto, $ipbrick_props[0]->mensagem, $system_email,'180');
    error_log (date("y-m-d/H:i:s",time())." - 'AUTO CONFIGURATION - Change destination address to send alets' \n", 3, "/opt/system/log/system.log");
    $dbipbrickconfig->updateIpbrickMonitor ($system_email, $user_email, "yes", "yes");

    /*****EmailNotifyLandingPage***/
    include_once ("/root/execCommand.cfg");
    $cloudcontrollerip = "csc-ucoip-pt.ipbrick.com";//Used on IBM MarketPlace TODO: put in config file
    ini_set("soap.wsdl_cache_enabled", "0");
    $server = $cloudcontrollerip;
    $class = 'SystemWS';
    $wsdl_url = "http://".$server.'/webservice/ws-server.php?module='.$class.'&wsdl';
    $location = "http://".$server."/webservice/ws-server.php?module=".$class;
    $uri = "urn:".$class;
    $context = stream_context_create([
        'ssl' => [
            // set some SSL/TLS specific options
            'verify_peer' => false,
            'verify_peer_name' => false,
            'trace' => true,
            'allow_self_signed' => true
        ]
    ]);

    try{
    //$client = new SOAPClient($wsdl_url, array("location" => $location, "uri" => $uri,'exceptions' => 1,'trace'=>1,'encoding' => 'UTF-8'));
    $client = new SOAPClient($wsdl_url, array('location' => $location,'uri' => $uri,'stream_context' => $context,'exceptions' => 1,'trace' => 1,'encoding' => 'UTF-8'));
    $emailparams['name'] = $name;
    $emailparams['email'] = $mail;
    $emailparams['company'] = $company;
    $emailparams['domain'] = $domain;
    $emailparams['mailinglist'] = $cb1;
    $emailparams['cloudprovider'] = $cloudprovider;
    //$key = base64_encode("kljew9798unkndsfdhfyuiwrw897987ijhnmcnadad53frgt4422dds78ej378");
    $key = base64_encode("li6drigctpolzudpkq3hlbg66c32uhquv1cw6qdvxd7cu712dioar8l04odz71");

    $resultws = $client->EmailNotifyLandingPageWS($key,$emailparams);
    if(!empty($resultws) && $resultws->result->resultcode == 1) {
        error_log (date("y-m-d/H:i:s",time())." - 'AUTO CONFIGURATION - EmailNotifyLandingPageWS - SUCCESS' \n", 3, "/opt/system/log/system.log");
    } else {
        error_log (date("y-m-d/H:i:s",time())." - 'AUTO CONFIGURATION - EmailNotifyLandingPageWS - FAIL - code (".print_r($resultws->result->errorcode,true).")' \n", 3, "/opt/system/log/system.log");
    }
    } catch (Exception $e) {
        error_log (date("y-m-d/H:i:s",time())." - 'AUTO CONFIGURATION - EmailNotifyLandingPageWS - FAIL' \n", 3, "/opt/system/log/system.log");
    }
    //artelecom specification
    //execAsRoot ("sed -i -e 's/dhclient\ eth1//g'  /etc/rc.local");
    //execAsRoot ("sed -i 's/.*default.*//g'  /etc/rc.local");
    exec ("php /opt/system/scripts/system_roles.php NETCONF 1");
    exec ("php /opt/system/scripts/system_roles.php CLOUD 1");
    
   /* exec ("insserv ejabberd");
    exec ("insserv rtpproxy");*/
}

$root_password = "";

if ($params['root_password']!="") {
    $root_password = $params['root_password'];
}

if (is_file ("/root/.slvm") && $params['runAs'] != "networkconfig") {
    $root_password = exec("cat /root/.slvm | grep \"OS_PASSWORD\" | awk -F \"=\" {'print $2'}");
    exec("mv /root/.slvm /root/.slvm2 2> /dev/null");
}

if ($root_password != '') {
   $root_password = trim($root_password,'"');
   error_log (date("y-m-d/H:i:s",time())." - 'AUTO CONFIGURATION - Change passwords of system users (root/operator/receivedmail/sentmail/spam/voipcdr), WEB Interface Admin and CAFE administrator' \n", 3, "/opt/system/log/system.log");
   $result_password = changePasswords ($root_password);
}

if ($params['runAs'] == "creatednsentry") {
    $dbsessao->deletesessao ();
    error_log (date("y-m-d/H:i:s",time())." - 'AUTO CONFIGURATION - Replace defaultconfig' \n", 3, "/opt/system/log/system.log");
    //Replace defaultconfig on first apply configurations
    if(!is_file ("/etc/.autoconfig_lock2")) {
	$db_pass_defaultconfig = "S4b3d0R141nF1n1t4t0unl0ckc0nf1g";
        exec("ls -lat /opt/system/backupDB/ | awk {'print $9'} | grep '.zip'",$confs);
        //exec("cp -rp /opt/system/backupDB/".$confs[0]." /opt/system/include/repor_base/defaultconfig.zip");
        $filetoreplace = "/tmp/00000000000000.zip";
        exec("cp /opt/system/backupDB/".$confs[0]." $filetoreplace > /dev/null 2> /dev/null");
        exec("rm /tmp/dump_systemconf.sql > /dev/null 2> /dev/null; rm /tmp/configurations.zip > /dev/null 2> /dev/null; rm $filetoreplace-id.bin > /dev/null 2> /dev/null;");
        exec("cp " . $filetoreplace . " " . $filetoreplace . ".temp > /dev/null 2> /dev/null");
        exec("unzip -j -o -d /tmp/ -P $db_pass_defaultconfig $filetoreplace.temp > /dev/null 2> /dev/null");
        exec("head -n 86 /tmp/configurations.zip > " . $filetoreplace . "-id.bin");
        exec("
            base64 -d " . $filetoreplace . "-id.bin >/dev/null 2> /dev/null
            tail -n +87 /tmp/configurations.zip > " . $filetoreplace . ".temp
            PASS=`tail -c +2295 " . $filetoreplace . "-id.bin | head -c 3358 | md5sum | awk '{print $1}'`
            unzip -j -o -d /tmp/ -P \$PASS " . $filetoreplace . ".temp > /dev/null 2> /dev/null
        ");
        exec("rm -f ".$filetoreplace.".temp > /dev/null 2> /dev/null");
        exec("rm -f ".$filetoreplace."-id.bin > /dev/null 2> /dev/null");
        exec("rm -f /tmp/configurations.zip > /dev/null 2> /dev/null");
        exec("rm -f /tmp/control > /dev/null 2> /dev/null");
        exec ("rm -f /opt/system/include/repor_base/defaultconfig.zip > /dev/null 2> /dev/null");
        exec ("zip -P ".$db_pass_defaultconfig." /tmp/configurations.zip /tmp/dump_systemconf.sql > /dev/null 2> /dev/null");
        exec ("echo \"Version: 1.0\" > /tmp/control");
        exec ("zip -P ".$db_pass_defaultconfig." /opt/system/include/repor_base/defaultconfig.zip /tmp/configurations.zip control > /dev/null 2> /dev/null");
        exec ("chmod 750 /opt/system/include/repor_base/defaultconfig.zip > /dev/null 2> /dev/null");
        exec ("chown 0.33 /opt/system/include/repor_base/defaultconfig.zip > /dev/null 2> /dev/null");
        exec ("rm -f  /tmp/dump_systemconf.sql > /dev/null 2> /dev/null");
        exec ("rm -f  /tmp/configurations.zip  > /dev/null 2> /dev/null");
        exec ("rm -f  /tmp/control > /dev/null 2> /dev/null");
        exec ("rm -f  /tmp/dump_system.sql > /dev/null 2> /dev/null");
        exec ("PGPASSWORD=".$dbpass_postgres." psql -p ".$dbport." -U postgres template1 -c \"drop database tempdefaultconfig\" > /dev/null 2> /dev/null");
        $checksum = "rm -f /tmp/sha1sum
        find /opt/system/ -group 33 -iregex '.*\.php$' -exec sha1sum -b {} \; | grep -v erro_acesso1.php | grep -v erro_acesso2.php | grep -v '/opt/system/gerados' | grep -v '/opt/system/backupDB' | grep -v '/opt/system/backupSYS' | grep -v '/opt/system/ReleaseNotes' >> /tmp/sha1sum
        find /opt/system/ -group 33 -iregex '.*\.phpclass$' -exec sha1sum -b {} \; >> /tmp/sha1sum
        sha1sum -b /opt/system/site.conf >> /tmp/sha1sum
        sha1sum -b /opt/system/include/repor_base/defaultconfig.zip >> /tmp/sha1sum
        zip -P SHA1SUMIPBRICKCHECK /opt/system/application.dat /tmp/sha1sum
        rm -f /tmp/sha1sum
        chmod 750 /opt/system/application.dat
        chown 0.33 /opt/system/application.dat";
        exec($checksum);
        exec ("touch /etc/.autoconfig_lock2");
    }
    /*****Adds DNS entry on UCOIP.PT***/ 

/*
    $mac= exec ("ifconfig eth1 | grep HWaddr | awk {'print $5'}");
    $url="http://checkip.amazonaws.com";
    // init curl object        
    $ch = curl_init();
    // define options
    $optArray = array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true
    );
    // apply those options
    curl_setopt_array($ch, $optArray);
    // execute request and get response
    $publicIP = trim(curl_exec($ch));
*/
//    $publicIP = exec("curl http://169.254.169.254/latest/meta-data/public-ipv4");

    $publicIP = exec ("dig +short myip.opendns.com @resolver1.opendns.com");
    //for testing purposes only
    //$publicIP = "172.18.203.121";
    $cloudcontrollerip = "csc-ucoip-pt.ipbrick.com";//Used on IBM MarketPlace TODO: put in config file
    ini_set("soap.wsdl_cache_enabled", "0");
    $server = $cloudcontrollerip;
    $class = 'SystemWS';
    $wsdl_url = "https://".$server.'/webservice/ws-server.php?module='.$class.'&wsdl';
    $location = "https://".$server."/webservice/ws-server.php?module=".$class;
    $uri = "urn:".$class;
    $context = stream_context_create([
        'ssl' => [
            // set some SSL/TLS specific options
            'verify_peer' => false,
            'verify_peer_name' => false,
            'trace' => true,
            'allow_self_signed' => true
        ]
    ]);
    try{
    //$client = new SOAPClient($wsdl_url, array("location" => $location, "uri" => $uri,'exceptions' => 1,'trace'=>1,'encoding' => 'UTF-8'));
    $client = new SOAPClient($wsdl_url, array('location' => $location,'uri' => $uri,'stream_context' => $context,'exceptions' => 1,'trace' => 1,'encoding' => 'UTF-8'));
    $dnsparams['action'] = "insert";
    $dnsparams['publicIP'] = $publicIP;
    $dnsparams['domain'] = $system_domain;
    $dnsparams['hostname'] = $system_hostname;
    //$key = base64_encode("kljew9798unkndsfdhfyuiwrw897987ijhnmcnadad53frgt4422dds78ej378");
    $key = base64_encode("li6drigctpolzudpkq3hlbg66c32uhquv1cw6qdvxd7cu712dioar8l04odz71");
    $resultws = $client->dnsManagerWS($key,$dnsparams);
    if(!empty($resultws) && $resultws->result->resultcode == 1) {
        error_log (date("y-m-d/H:i:s",time())." - 'AUTO CONFIGURATION - Adds DNS entry on UCOIP.PT - SUCCESS' \n", 3, "/opt/system/log/system.log"); 
    } else {
    	error_log (date("y-m-d/H:i:s",time())." - 'AUTO CONFIGURATION - Adds DNS entry on UCOIP.PT - FAIL - code (".print_r($resultws->result->errorcode,true).")' \n", 3, "/opt/system/log/system.log"); 
    }
    } catch (Exception $e) {
    	error_log (date("y-m-d/H:i:s",time())." - 'AUTO CONFIGURATION - Adds DNS entry on UCOIP.PT - FAIL' \n", 3, "/opt/system/log/system.log");
    }  
    exec ("sed -i -e 's/php\ \/opt\/system\/scripts\/autoconfig.php\ runAs=\"creatednsentry\"//g'  /etc/rc.local");
    //sleep (300);
    sleep (30);  //alterado para testes

    $cmd = "hostname -d | awk -F '.' '{print $(NF-1)\".\"\$NF}'";
    exec ($cmd, $result);
    $tld = $result[0];
    if ($tld == "ucoip.net" && !is_dir ("/etc/letsencrypt/live")) exec ("/opt/apps-scripts.d/letsencrypt.php");
    /*****EmailNotifyProvisioningWS***/ 
    try{
    include_once ("/root/execCommand.cfg");
    $root_password = exec("cat /root/.slvm2 | grep \"OS_PASSWORD\" | awk -F \"=\" {'print $2'}");
    exec("rm /root/.slvm2 2> /dev/null");
    $emailparams['name'] = $name;
    $emailparams['email'] = $mail;
    $emailparams['company'] = $company;
    $emailparams['domain'] = $domain;
    $emailparams['fqdn'] = exec ("hostname -f");
    $emailparams['ip'] =  $publicIP;
    $emailparams['adminpass'] = $root_password;
    $emailparams['cloudprovider'] = $cloudprovider;
    $resultws = $client->EmailNotifyProvisioningWS($key,$emailparams);
    if(!empty($resultws) && $resultws->result->resultcode == 1) {
        error_log (date("y-m-d/H:i:s",time())." - 'AUTO CONFIGURATION - EmailNotifyProvisioningWS - SUCCESS' \n", 3, "/opt/system/log/system.log");
    } else {
        error_log (date("y-m-d/H:i:s",time())." - 'AUTO CONFIGURATION - EmailNotifyProvisioningWS - FAIL - code (".print_r($resultws->result->errorcode,true).")' \n", 3, "/opt/system/log/system.log");
    }
    } catch (Exception $e) {
        error_log (date("y-m-d/H:i:s",time())." - 'AUTO CONFIGURATION - EmailNotifyProvisioningWS - FAIL' \n", 3, "/opt/system/log/system.log");
    }
}

if ($params['runAs'] != "creatednsentry" && $params['runAs'] != "networkconfig") { //Apply Configurations
    error_log (date("y-m-d/H:i:s",time())." - 'AUTO CONFIGURATION - Apply Configurations' \n", 3, "/opt/system/log/system.log");
    
    $ipbrickfqdn = '127.0.0.1';
    $class = 'SystemWS';
    $wsdl_url = "https://".$ipbrickfqdn.'/webservice/'.$class.'.wsdl';
    $location = "https://".$ipbrickfqdn."/webservice/ws-srv.php?module=".$class;
    $args = array($_xmlrpc_default_login,base64_encode($_xmlrpc_default_key),"Customize network settings.");
    $context = stream_context_create([
            'ssl' => [
                            // set some SSL/TLS specific options
                             'verify_peer' => false,
                                     'verify_peer_name' => false,
                                     'trace' => true,
                                             'allow_self_signed' => true
                                                 ]
                                                 ]);
    $client = new SOAPClient($wsdl_url, array("location" => $location, "uri" => $uri,'stream_context' => $context, 'exceptions' => 0,'encoding' => 'UTF-8'));
    $client->apply_conf_sync($args);
    /*
    $args = array($_xmlrpc_default_login,base64_encode($_xmlrpc_default_key),"Customize network settings.");
    $client = new SOAPClient("https://127.0.0.1/webservice/SystemmanagerWS.wsdl", array('location' => "https://127.0.0.1/webservice/ws-srv.php?module=SystemmanagerWS",
    'uri' => 'urn:SystemmanagerWS',
    'exceptions' => 0,
    'encoding' => 'UTF-8'));
    $client->apply_conf_sync($args);*/
    error_log (date("y-m-d/H:i:s",time())." - 'AUTO CONFIGURATION - Apply Configurations - END' \n", 3, "/opt/system/log/system.log");
//} else remove_provisioning_page ();
}
if ($params['runAs'] == "autoconfig") {
    exec ("rm /tmp/.autoconf_running.lock 2> /dev/null");
    exec ("touch /etc/.autoconfig_lock");
}
exit (0);  