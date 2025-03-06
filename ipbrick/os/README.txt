#########################################################################################################################
##                                                                                                                     ##
##                                                   IPBRICK-DEVOPS                                                    ##
##                                                                                                                     ##
##                                                                                                                     ##
##                                                                                    IPBRICK by EXPANDINDUSTRIA 2025  ##
#########################################################################################################################

#########################################################################################################################
##                                                                                                                     ##
##                                               PASSWORDS IPBRICK_DB                                                  ##
##                                                                                                                     ##
#########################################################################################################################
root@ipbrick:~# /opt/system/scripts/systemtools.php -u all
password: QoJv96V8RjLd#mP4st
Array
(
    [db_postgres_pw] => c50fcebdb6517f89f20f311ece12745d
    [db_asterisk_pw] => ae0aebbb8334651e43ce4813383f52bf
    [db_dbdoc_pw] => d114e1526f2431abbd43fa7c6ba06b95
    [db_ejabberd_pw] => 9eabb7d232aa5ca496affa774f596119
    [db_smstats_pw] => 7790cf4e9686c2817543269d615b529a
    [db_systemconf_pw] => 36378692305c5cba9a33984c88b26ac9
    [db_systemsoft_pw] => bcdbb5bd29261cebaf6732f0ee32a371
    [db_monitoring_pw] => cd25fa53490275db19ecf5e18ea715c4
    [db_kamailio_pw] => 4eb0958755ccf9566fcc8c04658e560d
    [db_sms_pw] => 73fa00b228304451b3e45baa06d1cc0a
    [db_voicemail_pw] => fbac2909f2cffd9f1da248aec874aff4
    [db_voipstats_pw] => f14bf1a6c88ceb76dee7f65dbb16df2f
    [db_bacula_pw] => 03e7f844b611c268e4f66cf6de765063
    [db_cafe_pw] => 33d6fc3940129d7aef529f0f1d8e0961
    [db_sogo_pw] => 455f86cd20c9952e15054af63332bb12
    [db_bots_pw] => cd787fe5cb06001c0190029762f40ffc
    [db_twofa_pw] => 3812b74b441be59c191388f9ac1e2751
    [db_contactcenter_pw] => 858e39f160969553ea3ac65f64a2e009
    [db_fail2ban_pw] => cd787fe5cb06001c0190029762f40ffc
    [ldap_biometry_pw] => d7d7ccd71aef736decfec7923b0054ed
    [ldap_contactsmanager_pw] => a18a86ba35833b753e2cf27b0c2e9669
    [ldap_manager_pw] => 3e8c630c70589c716102467c6c20fa04
    [ldap_reader_pw] => 56411e7b7ee533db1ebaadd218a485e2
    [ldap_smbmanager_pw] => 90fe56bf3baf484556dead841052a3b4
    [ldap_sugarcrmmanager_pw] => 18c1168060e28d22795a8e813d89892f
    [ldap_ucoipreader_pw] => Uco1pR3ad3rP4S5
    [cfg_file_pw] => 7883d86ca55612b7814f6872e1f0d0cd
    [sys_astmanager_pw] => 57921d835e654f0c5368457ab3db0efe
    [sys_fop_pw] => fa560babe99eda5aef7d19b7cb5bf631
    [sys_cdr_pw] => 7b7666ef8a86c9f2d2bbf96dfcc1b3d6
    [sys_moheventsd_pw] => 90382bdf1a0ac90aa141506c84871d44
)

#########################################################################################################################
##                                                                                                                     ##
##                                             INSTRUÇÕES DO PROVISIONAMENTO                                           ##
##                                                                                                                     ##
#########################################################################################################################
1. Edite o devopsconfig.php
    - Após editar o ficheiro 'devopsconfig.php' salve este dentro do servidor no seguinte diretório:
        '''
        testbed-devsecpaas/Software/opt/devops/scripts/devopsconfig.php
        '''
        
Para poderes avançar e testar os pontos seguintes, recomendava:

1 - copiares os teus desenvolvimentos para a maquina

2 - entrar na BD systemconf e ativar a flag "DEVOPS_DOCKER_COMPOSE"
root@ipbrick:~#
root@ipbrick:~# dbconnect systemconf
Password: QoJv96V8RjLd#mP4st
psql (15.10 (Debian 15.10-0+deb12u1))
Type "help" for help.

systemconf=> UPDATE alteracao SET alterado='t' WHERE servico='DEVOPS_DOCKER_COMPOSE';
UPDATE 1
systemconf=>
systemconf=>

3 - Apply Configurations

4 - Verificar se o ficheiro /opt/devops/docker-compose/autoconfig_docker-compose.yml foi gerado com o conteudo pretendido. Se sim avançar, senão repetir os passos anteriores.

5 - executar o script devopsconfig.php com o seguinte parametro:
  '''
  php /opt/devops/scripts/devopsconfig.php --runAs="dockersconfigure"
  '''

6 - Validar que os containers keycloak e Outline estão a funcionar conforme pretendido
Um abraço,

Sempre que houver alterações dentro do /opt/system é necessário correr esse código:
  '''
  php /opt/system/scripts/updatedb/defaultconfig.php
  '''

#########################################################################################################################
##                                                                                                                     ##
##                                                  DOCKER UP DEMOVL                                                   ##
##                                                                                                                     ##
#########################################################################################################################
Para testar as configurações do autoconfig_docker.yml localmente dentro do servidor:
  docker compose -f autoconfig_docker-compose.yml up -d && docker network connect bridge keycloak_server && docker network connect bridge outline_server

#########################################################################################################################
##                                                                                                                     ##
##                                                CONFIG APACHE2-SERVER                                                ##
##                                                                                                                     ##
#########################################################################################################################
  <Proxy *>
    Order deny,allow
    Allow from all
  </Proxy>
  ProxyPreserveHost On
  ProxyPass / http://localhost:15240/
  ProxyPassReverse / http://localhost:15240/
  <Location "/realtime/">
    ProxyPass ws://localhost:15240/realtime/
    ProxyPassReverse ws://localhost:15240/realtime/
  </Location>
  <Location "/collaboration/">
    ProxyPass ws://localhost:15240/collaboration/
    ProxyPassReverse ws://localhost:15240/collaboration/
  </Location>
</VirtualHost>

#########################################################################################################################
##                                                                                                                     ##
##                                                      CONFIG LDAP                                                    ##
##                                                                                                                     ##
#########################################################################################################################
  # --------------- IMPORT ---------------
  IMPORT_SECRET_KEY: "PkIjqEKDZIJNtBFfln21SH9cHKyTYl80"
  IMPORT_BIND_CREDENTIAL: "56411e7b7ee533db1ebaadd218a485e2"
  IMPORT_LDAP_IP: "ldap://172.18.203.225"
  IMPORT_BIND_DN: "cn=Reader,dc=demovl,dc=ucoip,dc=pt"
  IMPORT_USERS_DN: "ou=users,dc=demovl,dc=ucoip,dc=pt"

#########################################################################################################################
##                                                                                                                     ##
##                                                      LOGS DE INSTALAÇÃO                                             ##
##                                                                                                                     ##
#########################################################################################################################
tail -f  /opt/system/log/system.log  -f /opt/devops/log/devops.log

## LOGS CONFIGURADOS DENTRO DO DEVOPS CONFIG
error_log(date("c")." - DEBUG:".__FUNCTION__."@".__FILE__.":".__LINE__." VAR»{$cmd}= ".print_r($cmd, "t")."\n",3,"/tmp/em.log");
