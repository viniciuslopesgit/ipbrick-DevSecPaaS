#!/bin/bash
#
#
echo "Enviando ficheiros .../opt/devops/docker-compose..."


check_error()
{
    if [ $? -ne 0 ]; then
        echo "Erro: $1"
        exit 1
    fi
}
sshpass -p 'QoJv96V8RjLd' scp dockercompose/docker-compose.yml operator@172.18.203.225:/opt/devops/docker-compose/autoconfig_docker-compose.yml
check_error "Erro ao enviar docker-compose.yml"
sshpass -p 'QoJv96V8RjLd' scp dockercompose/init.sql operator@172.18.203.225:/opt/devops/docker-compose
check_error "Erro ao enviar init.sql"
sshpass -p 'QoJv96V8RjLd' scp -r dockercompose/keycloak/ operator@172.18.203.225:/opt/devops/docker-compose
check_error "Erro ao enviar diretório keycloak"

echo "Ficheiros enviados com sucesso!"