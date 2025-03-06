<?php
function export_devops_docker_compose_yml ()
{
    global $_path;
    global $_path_gerados;
    global $_dn_base;
    global $_dn_users;
    global $_srv_auth;
    global $_dbhostldap;
    global $servidor_dominio;
    global $dbautenticacao;
    global $dbapache;
    global $dbinterface;

    IpbLogMessage ("Generate new DEVOPS file docker-compose.yml \n");
    $template = "\n## Generated at ".date("Y-M-d H:m")."\n\n";
    $template .= file_get_contents ("/opt/system/include.d/include/devops/docker-compose/docker-compose.yml.template");
    $devops_website = $dbapache->getApacheByIdapache (156);
    $hostname = $devops_website[0]->servername;
    //Ticket #24 - Criar Virtualhost, Reverse Proxy e alias DNS para docker Grafana
    $grafana_website = $dbapache->getApacheByIdapache (158);
    $grafana_servername = $grafana_website[0]->servername;
    //Ticket #25 - Criar Virtualhost, Reverse Proxy e alias DNS para docker GitLab
    $gitlab_website = $dbapache->getApacheByIdapache (159);
    $gitlab_servername = $gitlab_website[0]->servername;
    //Ticket #21 - Criar Virtualhost, Reverse Proxy e alias DNS para docker WeKan
    $wekan_website = $dbapache->getApacheByIdapache (160);
    $wekan_servername = $wekan_website[0]->servername;
    //Ticket #26 - Criar Virtualhost, Reverse Proxy e alias DNS para docker Outline
    $outline_website = $dbapache->getApacheByIdapache (161);
    $outline_servername = $outline_website[0]->servername;
    $keycloak_website = $dbapache->getApacheByIdapache (162);
    $keycloak_servername = $keycloak_website[0]->servername;

    $passwords = getPasswords();
    
    $ldap_basedn_users = $_dn_users."".$_dn_base;           // LDAP_BASEDN             ou=users,dc=expo,dc=com
    $ldap_binddn = "cn=reader,".$_dn_base;                  // LDAP_BINDDN             cn=reader,dc=expo,dc=com
    $ldap_bindpw = $passwords["ldap_reader_pw"];            // LDAP_BINDPW             R3ad3rp4ss
    //para dentro dos docker não se pode passar localhost
    if ( $_dbhostldap == 'localhost') {
        $interface0 = $dbinterface->getInterfaceByInterface(0);
        $_dbhostldap = $interface0[0]->ip;
    }
    $template = str_replace ("---HOSTNAME---", $hostname, $template);
    $template = str_replace ("---DOMAIN---", $servidor_dominio, $template);
    $template = str_replace ("---LDAP_HOST---", $_dbhostldap, $template);
    $template = str_replace ("---LDAP_BIND_DN---", $ldap_binddn, $template);
    $template = str_replace ("---LDAP_PASSWORD---", $ldap_bindpw, $template);
    $template = str_replace ("---LDAP_BASE_DN---", $_dn_base, $template);
    $template = str_replace ("---LDAP_BASE_DN_USERS---", $ldap_basedn_users, $template);
    $template = str_replace ("---GRAFANA_SERVERNAME---", $grafana_servername, $template);    
    $template = str_replace ("---GITLAB_SERVERNAME---", $gitlab_servername, $template);
    $template = str_replace ("---WEKAN_SERVERNAME---", $wekan_servername, $template);
    $template = str_replace ("---OUTLINE_SERVERNAME---", $outline_servername, $template);
    $template = str_replace ("---KEYCLOAK_SERVERNAME---", $keycloak_servername, $template);
    file_put_contents($_path_gerados."devops_docker-compose.yml", $template);    
    return 1;
}

//Ticket #53 - Criar export, config e register para para gitlab_runner
function export_devops_docker_compose_gitlab_runner_yml ()
{
    global $_path;
    global $_path_gerados;
    global $_dn_base;
    global $_dn_users;
    global $_srv_auth;
    global $_dbhostldap;
    global $servidor_dominio;
    global $dbautenticacao;
    global $dbapache;
    global $dbinterface;
    global $dbdnsdef;
    IpbLogMessage ("Generate new DEVOPS file docker-compose_gitlab-runner.yml \n");
    $template = "\n## Generated at ".date("Y-M-d H:m")."\n\n";
    $template .= file_get_contents ("/opt/system/include.d/include/devops/docker-compose/docker-compose_gitlab-runner.yml.template");
    $dnsdef = $dbdnsdef->getDnsDef();
    $dns_server = $dnsdef[0]->ip;
    $gitlab_website = $dbapache->getApacheByIdapache (159);
    $gitlab_servername = $gitlab_website[0]->servername;
    $template = str_replace ("---DNS_SERVER---", $dns_server, $template);
    $template = str_replace ("---GITLAB_SERVERNAME---", $gitlab_servername, $template);
    file_put_contents($_path_gerados."devops_docker-compose_gitlab-runner.yml", $template);    
    return 1;
}

function export_devops_gitlab_runner_config_toml ()
{
    global $_path;
    global $_path_gerados;
    global $_dn_base;
    global $_dn_users;
    global $_srv_auth;
    global $_dbhostldap;
    global $servidor_dominio;
    global $dbautenticacao;
    global $dbapache;
    global $dbinterface;
    global $dbdnsdef;
    IpbLogMessage ("Generate new DEVOPS file gitlab-runner_config.toml \n");
    $template = "\n## Generated at ".date("Y-M-d H:m")."\n\n";
    $template .= file_get_contents ("/opt/system/include.d/include/devops/docker-compose/gitlab-runner_config.toml.template");
    $gitlab_website = $dbapache->getApacheByIdapache (159);
    $gitlab_servername = $gitlab_website[0]->servername;
    //Currently, only the first DNS server is set up. Future improvements might be necessary...
    $dnsdef = $dbdnsdef->getDnsDef();
    $dns_server = $dnsdef[0]->ip;
    $current_date = new DateTime();
    $token_date = $current_date->format('Y-m-d\TH:i:s\Z');   
    $template = str_replace ("---GITLAB_SERVERNAME---", $gitlab_servername, $template);
    $template = str_replace ("---DNS_SERVER---", $dns_server, $template);
    $template = str_replace ("---TOKEN_DATE---", $token_date, $template);
    file_put_contents($_path_gerados."devops_gitlab-runner_config.toml", $template);    
    return 1;
}