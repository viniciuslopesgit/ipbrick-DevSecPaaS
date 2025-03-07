#!/bin/bash
#
echo "Enviando ficheiros .../opt/devops/docker-compose..."
check_error()
{
    if [ $? -ne 0 ]; then
        echo "Erro: $1"
        exit 1
    fi
}
sshpass -p 'QoJv96V8RjLd' scp php/actualiza_def.php operator@172.18.203.225:/opt/system/include.d/include/devops/actualiza_def/actualiza_def.php
check_error "Erro ao enviar actualiza_def.php"
sshpass -p 'QoJv96V8RjLd' scp php/devopsconfig.php operator@172.18.203.225:/opt/devops/scripts/devopsconfig.php
check_error "Erro ao enviar devopsconfig.php"
sshpass -p 'QoJv96V8RjLd' scp php/export_docker-compose.php operator@172.18.203.225:/opt/system/include.d/include/devops/docker-compose/export_docker-compose.php
check_error "Erro ao enviar export_docker-compose.php"
sshpass -p 'QoJv96V8RjLd' scp dockercompose/docker-compose-template.yml operator@172.18.203.225:/opt/system/include.d/include/devops/docker-compose/docker-compose-template.yml 
check_error "Erro ao enviar docker-compose-template.yml"
sshpass -p 'QoJv96V8RjLd' scp dockercompose/docker-compose-template.yml operator@172.18.203.225:/opt/system/include.d/include/devops/docker-compose/docker-compose.yml.template
check_error "Erro ao enviar docker-compose.yml.template"
sshpass -p 'QoJv96V8RjLd' scp dockercompose/keycloak/imports/import_template.json operator@172.18.203.225:/opt/system/include.d/include/devops/docker-compose/import_template.json
check_error "Erro ao enviar import_template.json"
sshpass -p 'QoJv96V8RjLd' scp dockercompose/init.sql operator@172.18.203.225:/opt/devops/docker-compose
check_error "Erro ao enviar init.sql"
# sshpass -p 'QoJv96V8RjLd' scp -r dockercompose/keycloak/ operator@172.18.203.225:/opt/devops/docker-compose
# check_error "Erro ao enviar diretório keycloak"

echo "Ficheiros enviados com sucesso!"