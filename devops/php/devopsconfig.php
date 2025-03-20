#!/usr/bin/php -q
<?php
/**
 * <Modify configurations in devops scenarios>
 *
 * PHP version 8
 *
 * LICENSE: IPBRICK License
 *  This code is property of IPBRICK International.
 *
 *
 * @category   System  
 * @package    ipbrick
 * @author     IPBRICK <support@ipbrick.com>
 * @copyright  2000-2024 IPBRICK International
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
        //exit(0);
    }
}

//check the authenticity of the script
$included_files = get_included_files ();
if ($_SERVER["SCRIPT_FILENAME"] != "/opt/devops/scripts/devopsconfig.php" || $included_files[0] != "/opt/devops/scripts/devopsconfig.php" || count ($included_files) != 1) {
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
  $postfields="&f_host0=cafe.$domain&f_ip1_0=$f_ip[0]&f_ip2_0=$f_ip[1]&f_ip3_0=$f_ip[2]&f_ip4_0=$f_ip[3]&f_idhost0=".
  "&f_host1=im.$domain&f_ip1_1=$f_ip[0]&f_ip2_1=$f_ip[1]&f_ip3_1=$f_ip[2]&f_ip4_1=$f_ip[3]&f_idhost1=".
  "&f_host2=ipbrick.$domain&f_ip1_2=$f_ip[0]&f_ip2_2=$f_ip[1]&f_ip3_2=$f_ip[2]&f_ip4_2=$f_ip[3]&f_idhost2=".
  "&f_host3=ucoip.$domain&f_ip1_3=$f_ip[0]&f_ip2_3=$f_ip[1]&f_ip3_3=$f_ip[2]&f_ip4_3=$f_ip[3]&f_idhost3=".
  "&f_host4=voip.$domain&f_ip1_4=$f_ip[0]&f_ip2_4=$f_ip[1]&f_ip3_4=$f_ip[2]&f_ip4_4=$f_ip[3]&f_idhost4=".
  "&f_host5=webrtc.$domain&f_ip1_5=$f_ip[0]&f_ip2_5=$f_ip[1]&f_ip3_5=$f_ip[2]&f_ip4_5=$f_ip[3]&f_idhost5=&alterar=Modify";
  $url2="https://$system_canonical/corpo.php?pagina=dns_local_names_modify_acc&reloadpag=1&total_dns_hosts_number=6&total_dns_hosts_number_add=0&deldnshost=&iddeldnshost=-1$postfields";
  curl_setopt($ch, CURLOPT_URL, $url2);
  $content = curl_exec ($ch);
  curl_close ($ch);
}

//Ticket #61 - Geração automatica dos certificados Let's Encrypt
function devops_Lets_Encrypt_add_certificates ()
{
    global $bd,$dbcertificado,$dbcrontab;
    global $dbalteracao;
    if ($bd->conn != -1) {
        $query = "INSERT INTO certificate (cert_name,cert_authority,domains_associated,main_domain,email,accao) VALUES ('lets_devops','2', (SELECT string_agg(servername, ',' ORDER BY servername ASC) FROM apache WHERE idapache IN (1, 64, 128, 135, 146, 151, 156, 157, 158, 159, 160, 161, 200)), (select servername from apache where idapache=128), CONCAT('administrator@', (SELECT dominio FROM servidor)), 'I')";
        @pg_exec($bd->conn, $query);
    }
    $cert_id = $dbcertificado->getMaxCertificate ();
    // ativa o crontab para o renew dos certificados
    $dbcrontab->Turn_me_on_ByIdcrontab (28);
    $dbalteracao->setAlteracao ("CERTIFICADO");
    $dbalteracao->setAlteracao ("LETSENCRYPT");
    return $cert_id;
}

function devops_Lets_Encrypt_Update_Services($cert_id)
{
    global $dbcertificado,$dbcrontab,$dbapache;
    global $dbalteracao;
    $dbcertificado->updateCertificateServiceByCert (1, $cert_id);
    $dbcertificado->updateCertificateServiceByCert (2, $cert_id);
    $dbcertificado->updateCertificateServiceByCert (3, $cert_id);
    $dbcertificado->updateCertificateServiceByCert (4, $cert_id);
    $apache_sites = $dbapache->getApache();
    for ($j=0;$j<count((array)$apache_sites);$j++) {
        $dbapache->updateAccaoByIdapache($apache_sites[$j]->idapache);
    }
    $dbalteracao->setAlteracao ("POSTFIX_RELOAD");
    $dbalteracao->setAlteracao ("DOVECOT");
    return 1;
}

//Ticket #53 - Criar export, config e register para para gitlab_runner
class GitlabRunner {
    // Properties
    public $make;
    public $model;
    public $year;
    public static function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    public static function export_docker_compose_gitlab_runner_yml ($gitlab_servername, $register_token, $dns_server)
    {
        $_path_gerados = "/opt/system/gerados/";        
        error_log (date("y-m-d/H:i:s",time())." - 'DEVOPS AUTO CONFIGURATION - Generate new DEVOPS file docker-compose_gitlab-runner.yml' \n", 3, "/opt/system/log/system.log");
        $template = "\n## Generated at ".date("Y-M-d H:m")."\n\n";
        $template .= file_get_contents ("/opt/system/include.d/include/devops/docker-compose/docker-compose_gitlab-runner.yml.template");
        $template = str_replace ("---DNS_SERVER---", $dns_server, $template);         
        $template = str_replace ("---GITLAB_SERVERNAME---", $gitlab_servername, $template);
        $template = str_replace ("---REGISTRATION_TOKEN---", $register_token, $template);
        file_put_contents($_path_gerados."devops_docker-compose_gitlab-runner.yml", $template);        
        sleep (1);
        return 1;
    }
    public static function export_gitlab_runner_config_toml ($gitlab_servername, $register_token, $dns_server)
    {
        $_path_gerados = "/opt/system/gerados/";
        error_log (date("y-m-d/H:i:s",time())." - 'DEVOPS AUTO CONFIGURATION - Generate new DEVOPS file gitlab-runner_config.toml' \n", 3, "/opt/system/log/system.log");
        $template = "\n## Generated at ".date("Y-M-d H:m")."\n\n";
        $template .= file_get_contents ("/opt/system/include.d/include/devops/docker-compose/gitlab-runner_config.toml.template");
        error_log(date("c")." - DEBUG:".__FUNCTION__."@".__FILE__.":".__LINE__." VAR»{$template}= ".print_r($template, "t")."\n",3,"/tmp/em.log");
        $current_date = new DateTime();
        $token_date = $current_date->format('Y-m-d\TH:i:s\Z');    
        error_log(date("c")." - DEBUG:".__FUNCTION__."@".__FILE__.":".__LINE__." VAR»{$template}= ".print_r($template, "t")."\n",3,"/tmp/em.log");
        $template = str_replace ("---GITLAB_SERVERNAME---", $gitlab_servername, $template);
        $template = str_replace ("---REGISTRATION_TOKEN---", $register_token, $template);        
        $template = str_replace ("---TOKEN_DATE---", $token_date, $template);
        $template = str_replace ("---DNS_SERVER---", $dns_server, $template);                  
        file_put_contents($_path_gerados."devops_gitlab-runner_config.toml", $template);    
        sleep (1);
        error_log(date("c")." - DEBUG:".__FUNCTION__."@".__FILE__.":".__LINE__." VAR»{$template}= ".print_r($template, "t")."\n",3,"/tmp/em.log");
        return 1;
    }    
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
if ($params['runAs'] == "dockersinstall") {
    error_log (date("y-m-d/H:i:s",time())." - 'DEVOPS AUTO CONFIGURATION [dockersinstall] - starting...' \n", 3, "/opt/system/log/system.log");
    sleep (2);
    exec ("mkdir -pv /home1/_dockers >> /opt/devops/log/devops.log 2>> /opt/devops/log/devops.log");
    exec ("/etc/init.d/docker start");//Enable docker if not enabled
    sleep (2);
    error_log (date("y-m-d/H:i:s",time())." - 'DEVOPS AUTO CONFIGURATION [dockersinstall] - starting docker instalation ...' \n", 3, "/opt/system/log/system.log");
    //Ticket #45 - Necessário atualizar o docker do gitlab para a versão 17.3.7
    exec ("docker pull gitlab/gitlab-ce:17.3.7-ce.0  >> /opt/devops/log/devops.log 2>> /opt/devops/log/devops.log");
    error_log (date("y-m-d/H:i:s",time())." - 'DEVOPS AUTO CONFIGURATION [dockersinstall] - gitlab installed!' \n", 3, "/opt/system/log/system.log");
    sleep (2);
    exec ("docker pull portainer/portainer-ce:2.20.3 >> /opt/devops/log/devops.log 2>> /opt/devops/log/devops.log");
    error_log (date("y-m-d/H:i:s",time())." - 'DEVOPS AUTO CONFIGURATION [dockersinstall] - portainer installed!' \n", 3, "/opt/system/log/system.log");
    sleep (2);
    exec ("docker pull grafana/grafana:11.1.4 >> /opt/devops/log/devops.log 2>> /opt/devops/log/devops.log");
    error_log (date("y-m-d/H:i:s",time())." - 'DEVOPS AUTO CONFIGURATION [dockersinstall] - grafana installed!' \n", 3, "/opt/system/log/system.log");
    sleep (2);
    exec ("docker pull wekanteam/wekan:v7.59 >> /opt/devops/log/devops.log 2>> /opt/devops/log/devops.log");
    error_log (date("y-m-d/H:i:s",time())." - 'DEVOPS AUTO CONFIGURATION [dockersinstall] - wekan installed!' \n", 3, "/opt/system/log/system.log");
    sleep (2);
    exec ("docker pull outlinewiki/outline:0.82.0 >> /opt/devops/log/devops.log 2>> /opt/devops/log/devops.log");
    error_log (date("y-m-d/H:i:s",time())." - 'DEVOPS AUTO CONFIGURATION [dockersinstall] - outline installed!' \n", 3, "/opt/system/log/system.log");
    sleep (2);
    exec ("docker pull redis:7.4 >> /opt/devops/log/devops.log 2>> /opt/devops/log/devops.log");
    error_log (date("y-m-d/H:i:s",time())." - 'DEVOPS AUTO CONFIGURATION [dockersinstall] - redis installed!' \n", 3, "/opt/system/log/system.log");
    sleep (2);
    //Ticket #116 - Instalar imagem keycloak/keycloak:26.0 e adicionar docker-compose.yml para configuração da mesma
    exec ("docker pull keycloak/keycloak:26.0 >> /opt/devops/log/devops.log 2>> /opt/devops/log/devops.log");
    error_log (date("y-m-d/H:i:s",time())." - 'DEVOPS AUTO CONFIGURATION [dockersinstall] - keycloak installed!' \n", 3, "/opt/system/log/system.log");
    sleep (2);
    error_log (date("y-m-d/H:i:s",time())." - 'DEVOPS AUTO CONFIGURATION [dockersinstall] - docker instalation finished!!' \n", 3, "/opt/system/log/system.log");
    error_log (date("y-m-d/H:i:s",time())." - 'DEVOPS AUTO CONFIGURATION [dockersinstall] - END' \n", 3, "/opt/system/log/system.log");
}

if ($params['runAs'] == "dockersconfigure") {
    error_log (date("y-m-d/H:i:s",time())." - 'DEVOPS AUTO CONFIGURATION [dockersconfigure] - starting...' \n", 3, "/opt/system/log/system.log");
    sleep (2);
    $autoconfig_docker_compose = "/opt/devops/docker-compose/autoconfig_docker-compose.yml";
    if (file_exists($autoconfig_docker_compose)) {
        //echo "The file $autoconfig_docker_compose exists.";
        exec ("docker compose -f /opt/devops/docker-compose/autoconfig_docker-compose.yml up -d >> /opt/devops/log/devops.log 2>> /opt/devops/log/devops.log");
    } else {
        echo "The file docker-compose.yml $autoconfig_docker_compose does not exist!";
        error_log (date("y-m-d/H:i:s",time())." - 'DEVOPS AUTO CONFIGURATION [dockersconfigure] - Error: The file docker-compose.yml $autoconfig_docker_compose does not exist!' \n", 3, "/opt/system/log/system.log");
    }
    error_log (date("y-m-d/H:i:s",time())." - 'DEVOPS AUTO CONFIGURATION [dockersconfigure] - END' \n", 3, "/opt/system/log/system.log");
}

//Ticket #61 - Geração automatica dos certificados Let's Encrypt
if ($params['runAs'] == "letsencrypt") { //Apply Configurations
    error_log (date("y-m-d/H:i:s",time())." - 'DEVOPS AUTO CONFIGURATION [letsencrypt] - starting...' \n", 3, "/opt/system/log/system.log");
    error_log (date("y-m-d/H:i:s",time())." - 'DEVOPS AUTO CONFIGURATION [letsencrypt] - insert lets encrypt cert for the main hosts' \n", 3, "/opt/system/log/system.log");
    $cert_id = devops_Lets_Encrypt_add_certificates ();
    $ipbrickfqdn = '127.0.0.1';
    $class = 'SystemWS';
    $wsdl_url = "https://".$ipbrickfqdn.'/webservice/'.$class.'.wsdl';
    $location = "https://".$ipbrickfqdn."/webservice/ws-srv.php?module=".$class;
    $args = array($_xmlrpc_default_login,base64_encode($_xmlrpc_default_key),"Add Lets Encrypt Certificates");
    $context = stream_context_create([
            'ssl' => [
                            // set some SSL/TLS specific options
                             'verify_peer' => false,
                                     'verify_peer_name' => false,
                                     'trace' => true,
                                             'allow_self_signed' => true
                                                 ]
                                                 ]);
    ini_set("soap.wsdl_cache_enabled", "0"); // desabilita WSDL cache
    $client = new SOAPClient($wsdl_url, array("location" => $location, "uri" => $uri,'stream_context' => $context, 'exceptions' => 0,'encoding' => 'UTF-8'));
    $client->apply_conf_sync($args);
    //Ticket #72 - O metodo apply_conf_sync do webservice da IPBrick dá exit antes de realmente ter terminado
    error_log (date("y-m-d/H:i:s",time())." - 'DEVOPS AUTO CONFIGURATION [letsencrypt] - sleeping for 3 minutes...' \n", 3, "/opt/system/log/system.log");
    sleep (180);
    error_log (date("y-m-d/H:i:s",time())." - 'DEVOPS AUTO CONFIGURATION [letsencrypt] - enable certs for Apache, Mail, IM and WebRTC for cert_id: $cert_id' \n", 3, "/opt/system/log/system.log");
    devops_Lets_Encrypt_Update_Services($cert_id);
    $client = new SOAPClient($wsdl_url, array("location" => $location, "uri" => $uri,'stream_context' => $context, 'exceptions' => 0,'encoding' => 'UTF-8'));
    $client->apply_conf_sync($args);
    error_log (date("y-m-d/H:i:s",time())." - 'DEVOPS AUTO CONFIGURATION [letsencrypt] - END' \n", 3, "/opt/system/log/system.log");
}

if ($params['runAs'] == "gitlab-runner") { //gitlab-runner
    error_log (date("y-m-d/H:i:s",time())." - 'DEVOPS AUTO CONFIGURATION - gitlab-runner' \n", 3, "/opt/system/log/system.log");
    $ipbrickfqdn = '127.0.0.1';
    $class = 'SystemWS';
    //Ticket #64 - Erro a configurar gitlab-runner logo após o deploy do container do gitlab : GitLab is not responding
    $cmd = "docker exec gitlab gitlab-ctl status";
    $result = array();
    exec ($cmd, $result);
    error_log(date("c")." - DEBUG:".__FUNCTION__."@".__FILE__.":".__LINE__." VAR»{$cmd}= ".print_r($cmd, "t")."\n",3,"/tmp/em.log");
    error_log(date("c")." - DEBUG:".__FUNCTION__."@".__FILE__.":".__LINE__." VAR»{$result}= ".print_r($result, "t")."\n",3,"/tmp/em.log");
    sleep (30);
    $cmd = "docker exec gitlab gitlab-ctl status";
    $result = array();
    exec ($cmd, $result);
    error_log(date("c")." - DEBUG:".__FUNCTION__."@".__FILE__.":".__LINE__." VAR»{$cmd}= ".print_r($cmd, "t")."\n",3,"/tmp/em.log");
    error_log(date("c")." - DEBUG:".__FUNCTION__."@".__FILE__.":".__LINE__." VAR»{$result}= ".print_r($result, "t")."\n",3,"/tmp/em.log");
    //Ticket #58 - Instalar gitlab_runner no sistema IPBrick em vez de ser por Docker
    //Add user gitlab-runner
    $cmd = "useradd --system --shell /bin/bash --comment 'GitLab Runner' --create-home -b /home1/_locals gitlab-runner >> /opt/devops/log/devops.log 2>> /opt/devops/log/devops.log";
    $result = array();
    exec ($cmd, $result);
    error_log(date("c")." - DEBUG:".__FUNCTION__."@".__FILE__.":".__LINE__." VAR»{$cmd}= ".print_r($cmd, "t")."\n",3,"/tmp/em.log");
    error_log(date("c")." - DEBUG:".__FUNCTION__."@".__FILE__.":".__LINE__." VAR»{$result}= ".print_r($result, "t")."\n",3,"/tmp/em.log");
    //download the stable version of gitlab-runner
    $cmd = "wget -O /tmp/gitlab-runner_amd64.deb  https://gitlab-runner-downloads.s3.amazonaws.com/v17.6.0/deb/gitlab-runner_amd64.deb >> /opt/devops/log/devops.log 2>> /opt/devops/log/devops.log";
    $result = array();
    exec ($cmd, $result);
    error_log(date("c")." - DEBUG:".__FUNCTION__."@".__FILE__.":".__LINE__." VAR»{$cmd}= ".print_r($cmd, "t")."\n",3,"/tmp/em.log");
    error_log(date("c")." - DEBUG:".__FUNCTION__."@".__FILE__.":".__LINE__." VAR»{$result}= ".print_r($result, "t")."\n",3,"/tmp/em.log");
    //Install the stable version of gitlab-runner
    $cmd = "dpkg -i /tmp/gitlab-runner_amd64.deb; rm -v /tmp/gitlab-runner_amd64.deb; >> /opt/devops/log/devops.log 2>> /opt/devops/log/devops.log";
    $result = array();
    exec ($cmd, $result);
    error_log(date("c")." - DEBUG:".__FUNCTION__."@".__FILE__.":".__LINE__." VAR»{$cmd}= ".print_r($cmd, "t")."\n",3,"/tmp/em.log");
    error_log(date("c")." - DEBUG:".__FUNCTION__."@".__FILE__.":".__LINE__." VAR»{$result}= ".print_r($result, "t")."\n",3,"/tmp/em.log");
    $cmd = "systemctl status gitlab-runner";
    $result = array();
    exec ($cmd, $result);
    error_log(date("c")." - DEBUG:".__FUNCTION__."@".__FILE__.":".__LINE__." VAR»{$cmd}= ".print_r($cmd, "t")."\n",3,"/tmp/em.log");
    error_log(date("c")." - DEBUG:".__FUNCTION__."@".__FILE__.":".__LINE__." VAR»{$result}= ".print_r($result, "t")."\n",3,"/tmp/em.log");
    //waiting for gitlab-runner is running....
    sleep (30);
/*
$xtest1 ="
# Fetch the user with the username 'root'
root_user = User.find_by(username: 'root')

# Print user information
puts \"User ID: #{root_user.id}\"
puts \"Username: #{root_user.username}\"
puts \"Email: #{root_user.email}\"
puts \"Created at: #{root_user.created_at}\"
puts \"Updated at: #{root_user.updated_at}\"
";

file_put_contents("/tmp/xtest1.rb", $xtest1);
*/
    //Ticket #62 - gitlab-rails deixou de conseguir criar "Personal access tokens"
    $create_access_token_rb = "user = User.find_by(username: 'root')
token = user.personal_access_tokens.create!(name: 'create_runner_pat', scopes: ['create_runner'], expires_at: 1.days.from_now)
token.save!
puts \"Generated token: #{token.token}\"
# Open a file in write mode
File.open(\"/tmp/create_runner_pat.token\", \"w\") do |file|
  # Write the content of the variable to the file
  file.write(token.token)
end
";
    file_put_contents("/tmp/create_access_token.rb", $create_access_token_rb);

    $docker_cmd ="#!/bin/bash
echo \"Starting... \n\"
sleep 2
docker cp /tmp/create_access_token.rb gitlab:/tmp/create_access_token.rb 2>&1
sleep 1
#docker exec -it gitlab gitlab-rails runner '/tmp/create_access_token.rb'
docker exec gitlab gitlab-rails runner '/tmp/create_access_token.rb'
sleep 1
docker cp gitlab:/tmp/create_runner_pat.token /tmp/create_runner_pat.token
sleep 1
echo \"Done\n\";
";
    file_put_contents("/tmp/docker_run_cmds.sh", $docker_cmd);

    $cmd = "bash -x /tmp/docker_run_cmds.sh >/tmp/docker_run_cmds.log 2>/tmp/docker_run_cmds.log";
    $result = array();
    exec ($cmd, $result);
/*    
    error_log(date("c")." - DEBUG:".__FUNCTION__."@".__FILE__.":".__LINE__." VAR»{$cmd}= ".print_r($cmd, "t")."\n",3,"/tmp/em.log");
    error_log(date("c")." - DEBUG:".__FUNCTION__."@".__FILE__.":".__LINE__." VAR»{$result}= ".print_r($result, "t")."\n",3,"/tmp/em.log");

    sleep (5);


    $cmd = "docker exec -it gitlab gitlab-rails runner '/tmp/create_access_token.rb'";
    
    $result = array();
    exec ($cmd, $result);
*/
    error_log(date("c")." - DEBUG:".__FUNCTION__."@".__FILE__.":".__LINE__." VAR»{$cmd}= ".print_r($cmd, "t")."\n",3,"/tmp/em.log");
    error_log(date("c")." - DEBUG:".__FUNCTION__."@".__FILE__.":".__LINE__." VAR»{$result}= ".print_r($result, "t")."\n",3,"/tmp/em.log");
    sleep (5);
    //$Generated_token = str_replace('Generated token: ', '', $create_runner_pat[0]);
    $Generated_token = file_get_contents("/tmp/create_runner_pat.token");
    //Currently, only the first DNS server is set up. Future improvements might be necessary...
    $dnsdef = $dbdnsdef->getDnsDef();
    $dns_server = $dnsdef[0]->ip;
    //para dentro dos docker não se pode passar localhost
    if ($dns_server == '127.0.0.1') {
        $interface0 = $dbinterface->getInterfaceByInterface(0);
        $dns_server = $interface0[0]->ip;
    }
    $gitlab_website = $dbapache->getApacheByIdapache (159);
    $gitlab_servername = $gitlab_website[0]->servername;
    # added --insecure to test in DEV scenarios
    $cmd = "curl --silent --insecure --cacert /etc/apache2/ssl/apache.pem  --request POST --url \"https://".$gitlab_servername."/api/v4/user/runners\"  --data \"runner_type=instance_type\"  --data \"description=firstrunner\"  --header \"PRIVATE-TOKEN: ".$Generated_token."\"";
    $result = array();
    exec ($cmd, $result);
    error_log(date("c")." - DEBUG:".__FUNCTION__."@".__FILE__.":".__LINE__." VAR»{$cmd}= ".print_r($cmd, "t")."\n",3,"/tmp/em.log");
    error_log(date("c")." - DEBUG:".__FUNCTION__."@".__FILE__.":".__LINE__." VAR»{$result}= ".print_r($result, "t")."\n",3,"/tmp/em.log");
    $json_data = json_decode($result[0], true);
    $register_token = $json_data['token'];
    /*
    GitlabRunner::export_docker_compose_gitlab_runner_yml ($gitlab_servername, $register_token, $dns_server);

    if (file_exists($_path_gerados."devops_docker-compose_gitlab-runner.yml")) {
        //echo "The file $autoconfig_docker_compose exists.";
        exec ("docker compose -f ".$_path_gerados."devops_docker-compose_gitlab-runner.yml up -d >> /opt/devops/log/devops.log 2>> /opt/devops/log/devops.log");
    } else {
        echo "The file ".$_path_gerados."devops_docker-compose_gitlab-runner.yml does not exist.";
        error_log (date("y-m-d/H:i:s",time())." - 'DEVOPS AUTO CONFIGURATION - gitlab-runner - Error: the file ".$_path_gerados."devops_docker-compose_gitlab-runner.yml does not exist.' \n", 3, "/opt/system/log/system.log");
    }
    */
    //Ticket #58 - Instalar gitlab_runner no sistema IPBrick em vez de ser por Docker
    //Add user gitlab-runner
    $cmd = "gitlab-runner register --non-interactive --url https://".$gitlab_servername."  --token ".$register_token." --name=firstrunner --executor=docker --docker-image=docker:27.3.1-dind --docker-volumes=/var/run/docker.sock:/var/run/docker.sock --docker-dns=$dns_server >> /opt/devops/log/devops.log 2>> /opt/devops/log/devops.log";
    $result = array();
    exec ($cmd, $result);
    error_log(date("c")." - DEBUG:".__FUNCTION__."@".__FILE__.":".__LINE__." VAR»{$cmd}= ".print_r($cmd, "t")."\n",3,"/tmp/em.log");
    error_log(date("c")." - DEBUG:".__FUNCTION__."@".__FILE__.":".__LINE__." VAR»{$result}= ".print_r($result, "t")."\n",3,"/tmp/em.log");
    sleep (5);  
/*
    GitlabRunner::export_gitlab_runner_config_toml ($gitlab_servername, $register_token, $dns_server);

    if (file_exists($_path_gerados."devops_gitlab-runner_config.toml")) {
        echo "The file $autoconfig_docker_compose exists.";
        //Ticket #58 - Instalar gitlab_runner no sistema IPBrick em vez de ser por Docker
        //exec ("docker cp ".$_path_gerados."devops_gitlab-runner_config.toml gitlab-runner:/etc/gitlab-runner/config.toml >> /opt/devops/log/devops.log 2>> /opt/devops/log/devops.log");
        exec ("install -v -m 600 -o root -g root ".$_path_gerados."devops_gitlab-runner_config.toml /etc/gitlab-runner/config.toml >> /opt/devops/log/devops.log 2>> /opt/devops/log/devops.log");
    } else {
        echo "The file ".$_path_gerados."devops_gitlab-runner_config.toml does not exist.";
        error_log (date("y-m-d/H:i:s",time())." - 'DEVOPS AUTO CONFIGURATION - gitlab-runner - Error: the file ".$_path_gerados."devops_gitlab-runner_config.toml does not exist.' \n", 3, "/opt/system/log/system.log");
    }
*/
    //Ticket #62 - gitlab-rails deixou de conseguir criar "Personal access tokens"
    //For security reasons, removes the files containing the token
    //$cmd = "rm -v /tmp/create_access_token.rb; docker exec gitlab rm -f /tmp/create_access_token.rb";
    $result = array();
    exec ($cmd, $result);
    error_log (date("y-m-d/H:i:s",time())." - 'DEVOPS AUTO CONFIGURATION - gitlab-runner - END' \n", 3, "/opt/system/log/system.log");
}
if ($params['runAs'] == "remove_provisioning_page") { //Removes provisioning_page
    error_log (date("y-m-d/H:i:s",time())." - 'DEVOPS AUTO CONFIGURATION - Removes provisioning page - START' \n", 3, "/opt/system/log/system.log");

    remove_provisioning_page ();

    error_log (date("y-m-d/H:i:s",time())." - 'DEVOPS AUTO CONFIGURATION - Removes provisioning page - END' \n", 3, "/opt/system/log/system.log");
}
/*
if ($params['runAs'] == "autoconfig") {
    exec ("rm /tmp/.autoconf_running.lock 2> /dev/null");
    exec ("touch /etc/.autoconfig_lock");
}
*/
error_log (date("y-m-d/H:i:s",time())." - 'DEVOPS AUTO CONFIGURATION - Finished!!!' \n", 3, "/opt/system/log/system.log");
exit (0);  

