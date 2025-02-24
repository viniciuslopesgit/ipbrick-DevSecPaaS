#########################################################################################################################
##                                                                                                                     ##
##                                                    IPBRICK-DEVOPS                                                   ##
##                                                                                                                     ##
##                                                                                                                     ##
##                                                                                    IPBRICK by EXPANDINDUSTRIA 2025  ##
#########################################################################################################################

services:

#########################################################################################################################
##                                                                                                                     ##
##                                                       PORTAINER                                                     ##
##                                                                                                                     ##
#########################################################################################################################
  portainer:
    image: portainer/portainer-ce:2.20.3
    container_name: portainer_server
    ports:
      - 15200:9000
    volumes:
      - /var/run/docker.sock:/var/run/docker.sock
      - /home1/_dockers/portainer:/data
    restart: always

#########################################################################################################################
##                                                                                                                     ##
##                                                         GITLAB                                                      ##
##                                                                                                                     ##
#########################################################################################################################
  gitlab:
    image: gitlab/gitlab-ce:17.3.7-ce.0
    container_name: gitlab_server
    hostname: '---GITLAB_SERVERNAME---'
    environment:
      GITLAB_OMNIBUS_CONFIG: |
        hostname: 'gitlab.demovl.ucoip.pt'
        nginx['listen_https'] = false
        nginx['redirect_http_to_https'] = false
        nginx['listen_port'] = 8181
        gitlab_rails['initial_root_password']='R0laBill#'
        # LDAP configuration
        gitlab_rails['ldap_enabled'] = true
        gitlab_rails['ldap_label'] = 'LDAP'
        gitlab_rails['ldap_host'] = '192.168.225.254'
        gitlab_rails['ldap_port'] = 389
        gitlab_rails['ldap_uid'] = 'uid'
        gitlab_rails['ldap_method'] = 'plain' # 'ssl' or 'plain'
        gitlab_rails['ldap_bind_dn'] = 'cn=reader,dc=demovl,dc=ucoip,dc=pt'
        gitlab_rails['ldap_password'] = '56411e7b7ee533db1ebaadd218a485e2'
        gitlab_rails['ldap_allow_username_or_email_login'] = true
        gitlab_rails['ldap_base'] = 'dc=demovl,dc=ucoip,dc=pt'
    ports:
      - '15220:8181'
      - '15221:443'
      - '15222:22'
    volumes:
      - gitlab-install:/etc/gitlab
      - gitlab-logs:/var/log/gitlab
      - gitlab-data:/var/opt/gitlab
    shm_size: '256m'
    restart: always

#########################################################################################################################
##                                                                                                                     ##
##                                                        GRAFANA                                                      ##
##                                                                                                                     ##
#########################################################################################################################
  grafana:
    image: grafana/grafana:11.1.4
    container_name: grafana_server
    hostname: 'grafana.demovl.ucoip.pt'
    ports:
      - 15210:3000
    environment:
      - GF_DATABASE_TYPE=postgres
      - GF_DATABASE_HOST=postgres:5432
      - GF_DATABASE_NAME=grafana_db
      - GF_DATABASE_USER=admin
      - GF_DATABASE_PASSWORD=wkhuneTBF3F5gMUrtDaKs9Xe
      - GF_SECURITY_ADMIN_USER=admin
      - GF_SECURITY_ADMIN_PASSWORD=admin
      - GF_SERVER_SERVE_FROM_SUB_PATH=true
    depends_on:
      - postgres
    volumes:
      - grafana_storage:/var/lib/grafana
    restart: always

#########################################################################################################################
##                                                                                                                     ##
##                                                       KEYCLOAK                                                      ##
##                                                                                                                     ##
#########################################################################################################################
  keycloak:
    image: keycloak/keycloak:26.0
    container_name: keycloak_server
    environment:
      PROXY_ADDRESS_FORWARDING: true
      KC_DB: postgres
      KC_DB_URL: jdbc:postgresql://postgres:5432/keycloak_db
      KC_DB_USERNAME: admin
      KC_DB_PASSWORD: wkhuneTBF3F5gMUrtDaKs9Xe
      KC_TRANSACTION_XA_ENABLED: false
      KC_BOOTSTRAP_ADMIN_USERNAME: admin
      KC_BOOTSTRAP_ADMIN_PASSWORD: admin
      KC_LOG_LEVEL: DEBUG
      # --------------- IMPORT ---------------
      IMPORT_SECRET_KEY: "PkIjqEKDZIJNtBFfln21SH9cHKyTYl80"
      IMPORT_BIND_CREDENTIAL: "56411e7b7ee533db1ebaadd218a485e2"
      IMPORT_LDAP_IP: "ldap://172.18.203.225"
      IMPORT_BIND_DN: "cn=Reader,dc=demovl,dc=ucoip,dc=pt"
      IMPORT_USERS_DN: "ou=users,dc=demovl,dc=ucoip,dc=pt"
    command: >
      start --import-realm
      --proxy=edge
      --hostname="https://keycloak.demovl.ucoip.pt"
      --http-enabled=true
      --spi-cookie-same-site=None
    ports:
    - "15380:8080"
    - "15343:8443"
    volumes:
      - keycloak_data:/opt/keycloak/data
      - ./keycloak/imports:/opt/keycloak/data/import
    depends_on:
      - postgres
    restart: always

#########################################################################################################################
##                                                                                                                     ##
##                                                        OUTLINE                                                      ##
##                                                                                                                     ##
#########################################################################################################################
  outline:
    image: outlinewiki/outline:0.82.0
    container_name: outline_server
    environment:
      NODE_ENV: production
      UTILS_SECRET: bba0b0f57a3e8c26d95b4a897905b5f6e5cb7f9299f6f3f6c0214e9fa8b957a6
      SECRET_KEY: 32233a2ec875682c31ec1dd7a3fd875c6c403a6f03f670af1a99ee84800f416b
      DATABASE_URL: postgres://admin:wkhuneTBF3F5gMUrtDaKs9Xe@postgres:5432/outline_db
      DATABASE_CONNECTION_POOL_MIN:
      DATABASE_CONNECTION_POOL_MAX:
      PGSSLMODE: disable
      REDIS_URL: redis://redis:6379
      URL: https://outline.demovl.ucoip.pt/
      PORT: 3000
      # Keycloak OAuth
      OIDC_CLIENT_ID: outline
      OIDC_CLIENT_SECRET: "PkIjqEKDZIJNtBFfln21SH9cHKyTYl80"
      OIDC_AUTH_URI: http://keycloak.demovl.ucoip.pt:15380/realms/demovl/protocol/openid-connect/auth
      OIDC_TOKEN_URI: http://keycloak.demovl.ucoip.pt:15380/realms/demovl/protocol/openid-connect/token
      OIDC_USERINFO_URI: http://keycloak.demovl.ucoip.pt:15380/realms/demovl/protocol/openid-connect/userinfo
      OIDC_LOGOUT_URL: http://keycloak.demovl.ucoip.pt:15380/realms/demovl/protocol/openid-connect/logout
      OIDC_REDIRECT_URI: https://outline.demovl.ucoip.ptallback
      FORCE_HTTPS: true
      TRUST_PROXY: true
      WEB_CONCURRENCY: 1
      DEBUG: http
      LOG_LEVEL: info
      DEFAULT_LANGUAGE: en_US
      RATE_LIMITER_ENABLED: true
      RATE_LIMITER_REQUESTS: 1000
      RATE_LIMITER_DURATION_WINDOW: 60
      DEVELOPMENT_UNSAFE_INLINE_CSP: false
      # Local File Storage
      FILE_STORAGE: local
      FILE_STORAGE_UPLOAD_MAX_SIZE: 52428800 #50MB
      FILE_STORAGE_IMPORT_MAX_SIZE: 5120000
    ports:
      - "15240:3000"
    depends_on:
      - postgres
      - redis
      - keycloak
    restart: always

#########################################################################################################################
##                                                                                                                     ##
##                                                         WEKAN                                                       ##
##                                                                                                                     ##
#########################################################################################################################
  wekan:
    image: wekanteam/wekan:v7.59
    container_name: wekan_server
    restart: always
    networks:
      - wekan-tier
    ports:
      - 15230:8080
    environment:
      - WRITABLE_PATH=/data
      - MONGO_URL=mongodb://wekandb:27017/wekan
      #- ROOT_URL=http://localhost
      - ROOT_URL=https://wekan.demovl.ucoip.pt/
      - MAIL_URL=smtp://192.168.225.254:25/?ignoreTLS=true&tls={rejectUnauthorized:false}
      - MAIL_FROM=Wekan Notifications <noreply.wekan@domain.com>
      - WITH_API=true
      - RICHER_CARD_COMMENT_EDITOR=false
      - CARD_OPENED_WEBHOOK_ENABLED=false
      - BIGEVENTS_PATTERN=NONE
      - BROWSER_POLICY_ENABLED=true
      - DEFAULT_AUTHENTICATION_METHOD=ldap
      - LDAP_ENABLE=true
      - LDAP_PORT=389
      - LDAP_HOST=192.168.225.254
      - LDAP_USER_AUTHENTICATION=true
      - LDAP_USER_AUTHENTICATION_FIELD=uid
      - LDAP_BASEDN=ou=Users,dc=demovl,dc=ucoip,dc=pt
      - LDAP_RECONNECT=true
      - LDAP_AUTHENTIFICATION=true
      - LDAP_AUTHENTIFICATION_USERDN=cn=reader,dc=demovl,dc=ucoip,dc=pt
      - LDAP_AUTHENTIFICATION_PASSWORD=56411e7b7ee533db1ebaadd218a485e2
      - LDAP_LOG_ENABLED=true
      - LDAP_ENCRYPTION=false
      - LDAP_USER_SEARCH_FILTER=(&(objectClass=inetOrgPerson))
      - LDAP_USER_SEARCH_SCOPE=one
      - LDAP_USER_SEARCH_FIELD=uid
      - LDAP_USERNAME_FIELD=uid
      - LDAP_FULLNAME_FIELD=cn
      - LDAP_EMAIL_FIELD=mail
    depends_on:
      - wekandb
    volumes:
      - /etc/localtime:/etc/localtime:ro
      - wekan-files:/data:rw

#########################################################################################################################
##                                                                                                                     ##
##                                                       DATABASES                                                     ##
##                                                                                                                     ##
#########################################################################################################################
  # POSTGRES
  postgres:
    image: postgres:14.13-bookworm\\\\
    container_name: postgres_server
    ports:
      - "5432:5432"
    volumes:
      - database-data:/var/lib/postgresql/data
      - ./init.sql:/docker-entrypoint-initdb.d/init.sql
    environment:
      POSTGRES_USER: admin
      POSTGRES_PASSWORD: wkhuneTBF3F5gMUrtDaKs9Xe
      POSTGRES_DB: postgres
    restart: always

  # WEKAN_DB
  wekandb:
    image: mongo:6
    container_name: wekan_db
    restart: always
    command: mongod --logpath /dev/null --oplogSize 128 --quiet
    networks:
      - wekan-tier
    expose:
      - 27017
    volumes:
      - /etc/localtime:/etc/localtime:ro
      - wekan-db:/data/db
      - wekan-db-dump:/dump

  # REDIS
  redis:
    image: redis:7.4
    container_name: redis_db
    ports:
      - "6379:6379"
    volumes:
      - outline-redis-data:/data
    command: ["redis-server", "/redis.conf"]
    healthcheck:
      test: ["CMD", "redis-cli", "ping"]
      interval: 10s
      timeout: 30s
      retries: 3
    restart: always

#########################################################################################################################
##                                                                                                                     ##
##                                                        VOLUMES                                                      ##
##                                                                                                                     ##
#########################################################################################################################
volumes:
  database-data:
  keycloak_data:
  grafana_storage: {}
  gitlab-install: {}
  gitlab-logs: {}
  gitlab-data: {}
  wekan-files:
    driver: local
  wekan-db:
    driver: local
  wekan-db-dump:
    driver: local
  portainer_data:
    driver: local
  outline-db-data:
  outline-redis-data:

#########################################################################################################################
##                                                                                                                     ##
##                                                       NETWORKS                                                      ##
##                                                                                                                     ##
#########################################################################################################################
networks:
  wekan-tier:
    driver: bridge
