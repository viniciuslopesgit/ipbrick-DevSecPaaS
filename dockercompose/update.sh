#!/bin/bash
#
#
sshpass -p 'o14VCikrFvd2' scp docker-compose.yml operator@172.18.203.225:/opt/devops/docker-compose/autoconfig_docker-compose.yml && sshpass -p 'o14VCikrFvd2' scp init.sql operator@172.18.203.225:/opt/devops/docker-compose && sshpass -p 'o14VCikrFvd2' scp -r keycloak/ operator@172.18.203.225:/opt/devops/docker-compose
