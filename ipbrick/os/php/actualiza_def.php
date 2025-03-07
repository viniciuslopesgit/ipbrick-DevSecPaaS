<?php
global $ipsocket;
global $dbalteracao;
global $dbservidor;
global $dbgrupo_ldap;
global $dbligamailutilizadorgrupo_ldap;
global $alterado_dominio;
global $alterado_nome;
global $alterado_groupware;
global $alterado_utilizador;
global $_ipbrick_deamon_host;
global $_ipbrick_deamon_port;
global $_ipbrick_deamon_password;
global $_ipbrick_deamon_key_crypt;
global $_path;
global $_path_gerados;

//obtem nome e dominio servidor
$servidor = $dbservidor->getServidor();
$dominio = $servidor[0]->dominio;
$nome_serv = $servidor[0]->nome;
$alterados  = $dbalteracao->getServicosAlterado();

//Ticket #54 - Adicionar novas flags alteracao DEVOPS e DEVOPS_DOCKER_COMPOSE
$services_array= ['DOMINIO', 'AUTENTICACAO', 'CERTIFICADO','DEVOPS_DOCKER_COMPOSE'];
$alterado_devops = false;
for($i=0;$i<count((array)$alterados);$i++) if ( in_array($alterados[$i]->servico, $services_array)) $alterado_devops = true;

if ($alterado_devops) 
{
    IpbLogMessage("Updating DEVOPS Configuration...\n");
    include_once ($_path."include.d/include/devops/docker-compose/export_docker-compose.php");
    export_devops_docker_compose_yml();
    export_realm();
    $ipsocket->IpSocket_Write_Data ("install -vm 640 -o 0 -g 0 ".$_path_gerados."devops_docker-compose.yml  /opt/devops/docker-compose/autoconfig_docker-compose.yml");
    //Ticket #53 - Criar export e config para gitlab_runner
    //export_devops_docker_compose_gitlab_runner_yml ();    
    //$ipsocket->IpSocket_Write_Data ("install -vm 640 -o 0 -g 0 ".$_path_gerados."devops_docker-compose_gitlab-runner.yml  /opt/devops/docker-compose/autoconfig_docker-compose_gitlab-runner.yml.yml");
}

//Ticket #66 - Permitir que os utilizadores tenham acesso ao docker via consola
$alterado_user_grupo  = $dbalteracao->getAlteracaoByServico ('UTILIZADORGRUPO');

if ( $alterado_user_grupo[0]->alterado == 't' ) {
    //check if group devops has been modified
    $devops_groups = $dbgrupo_ldap->getGrupo_ldapByNome ("devops");
    //error_log(date("c")." - DEBUG:".__FUNCTION__."@".__FILE__.":".__LINE__." VAR»{$result}= ".print_r($devops_groups, "t")."\n",3,"/tmp/em.log");

    $devops_group = $devops_groups[0];
    //if ($devops_group->accao == 'U') {
        IpbLogMessage("Updating DEVOPS system group docker...\n");
        $devops_group_members = array();
        $devops_group_members = $dbligamailutilizadorgrupo_ldap->getLigaMailutilizadorGrupo_ldapByGrupo ($devops_group->idgrupo);
        //error_log(date("c")." - DEBUG:".__FUNCTION__."@".__FILE__.":".__LINE__." VAR»{$result}= ".print_r($devops_group_members, "t")."\n",3,"/tmp/em.log");
        $count_devops_group_members = count((array)$devops_group_members);

        $command = "getent group docker | cut -d':' -f4";
        execAsRoot ($command, $result);
        $system_docker_group = trim($result[0]);
        //error_log(date("c")." - DEBUG:".__FUNCTION__."@".__FILE__.":".__LINE__." VAR»{$result}= ".print_r($result, "t")."\n",3,"/tmp/em.log");
        $users_of_docker_system_group = array();
        $users_of_docker_system_group = explode(',',$system_docker_group);

        $users_of_devops_group = array();
        for($i=0; $i<$count_devops_group_members; $i++){
            $users_of_devops_group[] = $devops_group_members[$i]->login;
        }

        //get users to add to the group docker
        $users_to_add_to_docker_group_diff = array_diff($users_of_devops_group, $users_of_docker_system_group);
        $users_to_add_to_docker_group = array_values($users_to_add_to_docker_group_diff);
        $count_users_to_add_to_docker_group = count($users_to_add_to_docker_group);
        //error_log(date("c")." - DEBUG:".__FUNCTION__."@".__FILE__.":".__LINE__." VAR»{$result}= ".print_r($users_to_add_to_docker_group, "t")."\n",3,"/tmp/em.log");
        for($j=0; $j<$count_users_to_add_to_docker_group; $j++) {
            $ipsocket->IpSocket_Write_Data ("gpasswd --add ".$users_to_add_to_docker_group[$j]." docker");
            IpbLogMessage("Updating DEVOPS - adding ".$users_to_add_to_docker_group[$j]." to docker group\n");
        }

        //get users to remove from the group docker
        $users_to_remove_from_docker_group_diff = array_diff($users_of_docker_system_group, $users_of_devops_group);
        $users_to_remove_from_docker_group = array_values($users_to_remove_from_docker_group_diff);
        $count_users_to_remove_from_docker_group = count($users_to_remove_from_docker_group);
        //error_log(date("c")." - DEBUG:".__FUNCTION__."@".__FILE__.":".__LINE__." VAR»{$result}= ".print_r($users_to_remove_from_docker_group, "t")."\n",3,"/tmp/em.log");
        for($j=0; $j<$count_users_to_remove_from_docker_group; $j++) {
            if ($users_to_remove_from_docker_group[$j] == 'operator') {
                //do nothing 
            }else{
                $ipsocket->IpSocket_Write_Data ("gpasswd --delete ".$users_to_remove_from_docker_group[$j]." docker");
                IpbLogMessage("Updating DEVOPS - removing ".$users_to_remove_from_docker_group[$j]." from docker group\n");
            }

         }


    //}


}
?>
