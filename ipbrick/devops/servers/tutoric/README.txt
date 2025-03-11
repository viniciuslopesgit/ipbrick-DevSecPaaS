#########################################################################################################################
##                                                                                                                     ##
##                                                                                                                     ##
##                                          A2o + Steptutor v2 - IPBRICK-DEVOPS                                        ##
##                                                                                                                     ##
##                                                                                    IPBRICK by EXPANDINDUSTRIA 2025  ##
#########################################################################################################################

*** Alterações feitas no config.yml:

ALLOWED_HOSTS:
- a2o.ucoip.pt
AUTH_SECONDARY_BACKENDS:
- social_core.backends.open_id_connect.OpenIdConnectAuth
CADDY_HTTP_PORT: 15201
CMS_HOST: studio.tutoric.ucoip.pt                                                   # url do tutoric
CMS_OAUTH2_SECRET: Kp3kPpAwZvZEaWcgnaR7ztbg
CONTACT_EMAIL: ppereira@tutoric.ucoip.pt
CORS_ORIGIN_WHITELIST:
- https://tutoric.ucoip.pt                                                           # url do tutoric
- https://studio.tutoric.ucoip.pt                                                    # url do tutoric       
- https://apps.tutoric.ucoip.pt                                                      # url do tutoric
CSRF_TRUSTED_ORIGINS:
- https://tutoric.ucoip.pt                                                           # url do tutoric
- https://studio.tutoric.ucoip.pt                                                    # url do tutoric
- https://apps.tutoric.ucoip.pt                                                      # url do tutoric
ENABLE_CORS: true
ENABLE_HTTPS: true
ENABLE_THIRD_PARTY_AUTH: true
ENABLE_WEB_PROXY: false
ID: zUFsPlfNAcbbVeZ6RRIZC3Lj
JWT_RSA_PRIVATE_KEY: '-----BEGIN RSA PRIVATE KEY-----

  MIIEpAIBAAKCAQEA9mbzlICQgNRhdGoTeOxSjhzjTn3DwPr/s3j6S4XPBj/2RBrZ

  4Xxn6M0RSYZl8bCQZO3JIfYYbrx/fowLSwy7+IfmSCmFavqIA5seuDcfWEOJvejK

  6ImmWp9ZhSKYcmb2r8AAqCrXu7RUOgGY5NnjnjVNa5qiqWACX9RPMcyespN6rnUI

  YbsSBZHaqwDuvcIRhZHSjP7umWtWy0OIE1ti+aOq0hDqA579s1C+Ebkl9LRWk5x1

  qewDBg7ZIlA0MSzLUdwdcNVuk5W6N6pE649LVyFPUIrNCX27d0xF9yi7Otx8xrBx

  VA2M8ntwLmwnWHpn9GYShgXRWdgAN9YX7n5gAQIDAQABAoIBAAVG3CWMgUu5AKNJ

  GESpLvVrKfUG3XfXCD3UM+wFIBNesEvyFUNuch06swmTjsBXyyfWLc4D4q8Qa1yO

  7Dj9u1LNukFGXlyVo+8L1eMVDx9JRvxhrWLAe+cJ7ZVb0c71ZIgMAReY30jeSkV5

  vkf8YJMFSjSpOZ2N+E8MEWwcGTPyyNY2kaSpQhhiJ//KgSM4XUrqV2ZAI5BsUPdd

  yH5BGf1aJ+3+BN53B+RB9T3Sumw6qdVFORpUxJZrRU0fKzr3118KvNnTneE0Gybl

  Tv7fEf8IcVKNIIDdLkyXklUFm23yfgwB8WSudTxVwKm+arFZNzn4UUS4Xzwh3aGG

  MmSGtiECgYEA+Adxz/jKodi4E70hWZ4jQOi3YQEQ02fyjQPBPvt8FPaoGR51iftM

  XuPZQ19yawH1RqwLRV3o2nqfgiopPY3IZOAwSvaeJ6qkgxykiXsBnDrY5cSpHEo7

  tE32Xmt6nng5ILqsg96NNC8zUOTru1dkrOeFkot5qxIbzyXx2Yvx5XkCgYEA/lIf

  PsCCfqZ3ZJ/lbq/mOPx/jOPXKFCX0QrE+pAOrz1qauoQIx0LJS/7/m4QVHJR+ZKO

  1y8gFXMcN0BMG+oPP4XOf3kgnyI4A2T+Rggd5XJ6gf1AzXoYIxw563M/3BbUm+Mo

  ympPkc0K1fZnQxkWWvtzG2CqnmP9K29+qVKR1MkCgYBB70i0LsE/UStuI+MPvdhF

  Uxgcs3nTmViDYDIpGhWcRQ3ez5gTfDiLKSsCnAcAp16a0PWWFSbnnZ11rtuTv3M8

  TdTIuNLGXLirGhwraAW/kG1Ed4k1Og6xGeCarRvFsNQO/VELJUiITNvpb1GzVcIv

  rMR3dph7f67g3Id0e51skQKBgQCl98/eMuG2Z0qJR3QB/RbgX5+ZwWK7M4Uv7bhI

  0FRU9l6JcUCguaZ5WUw5aXiMs6JdndBMC7wDY4CpafOBUAktalQtik3IrBsj0/fA

  mjFweHoMdMqijahM2XHO/wJQzjFnniITnrdYrhgBM/GFr0yQiYI//qC6BwTEINnn

  BDSZSQKBgQCj5NQ/mdwA9O8HSphSQ7u+vVNgcjmg+L5InLYQNrR1F2JJatt8Kl9n

  HWUb2LEQxVNAlq1Pc17K6gAHRI8k/eksdNz9jNUfIltmOLkO5TNpV3UBVKWZSjDx

  WwdGYS8u0/RYSl7FBDkwJ1pLEk0czHIadA7t8OdRomDO7QkX80u9Cg==

  -----END RSA PRIVATE KEY-----'
LANGUAGE_CODE: en
LMS_HOST: tutoric.ucoip.pt
MEILISEARCH_API_KEY: c9f7a4c58b528ccc96d1ea092f34b5347171fa3222405b3a2d01095949a53d7a
MEILISEARCH_API_KEY_UID: f8c8ad79-44e0-4618-be2d-9368bfc567b8
MEILISEARCH_MASTER_KEY: r60pQ3VTEM8be5VqEedN8Nrj
MYSQL_ROOT_PASSWORD: LHc56FDu
OAUTH2_PROVIDER_BACKENDS:
- keycloak
OPENEDX_MYSQL_PASSWORD: jRGDjGod
OPENEDX_SECRET_KEY: 1FQ9djOJ3OU9l06I9NMgkYXP
PLATFORM_NAME: TutorIC
PLUGINS:
- indigo
- keycloak
- mfe
PLUGIN_INDEXES:
- https://overhang.io/tutor/main
SOCIAL_AUTH_OPENID_CONNECT_ENDPOINT: https://keycloak.tutoric.ucoip.pt/auth/realms/openedx-realm/protocol/openid-connect                        # url do tutoric
SOCIAL_AUTH_OPENID_CONNECT_EXTRA_DATA:
- email
- first_name
- last_name
SOCIAL_AUTH_OPENID_CONNECT_KEY: openedx
SOCIAL_AUTH_OPENID_CONNECT_SCOPE:
- openid
- email
- profile
SOCIAL_AUTH_OPENID_CONNECT_SECRET: Zm19jiV9AqeMCHDfvwRlPDEF9eEeCHxz




*** Alterações feitas no config.yml:

name: keycloak
version: 0.1.0
patches :
        common-env-features: |
                "ENABLE_THIRD_PARTY_AUTH" : true

        lms-env: |
                "THIRD_PARTY_AUTH_BACKENDS" : ["social_core.backends.keycloak.KeycloakOAuth2"]

        openedx-lms-common-settings: |
                SOCIAL_AUTH_KEYCLOAK_KEY= "openedx"
                SOCIAL_AUTH_KEYCLOAK_SECRET= "Zm19jiV9AqeMCHDfvwRlPDEF9eEeCHxz"
                SOCIAL_AUTH_KEYCLOAK_PUBLIC_KEY= "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA4ieht/zoSlQltM6C5k2W+np2AO5bJ/Te1izkIBQbN96yFWxlb8rzs1XjfcpJtDxFUQyHrHwanyR8HiGBnzpPs6y4ZjdTKaiBKH6h7MksMYJSqtASm+l4foE9260K4L+anx67JfMF1CtkpD391gfHMU386zt3OqYVJ/hCJRQrVHp8BEAyPz/CqsWeTqHn/uxua2Mx4KvdRBfB6bBfqJulGq1Yx/O7SpDHxQhswAp8JIG1UcFgiR4BUfM0qOkjXSlzgAX1QURG/FzMV9eMFa+zQmV2Dc4BYReaffqkbJhJ1vRRI68cV+6JcI4cFl6sDCg328C086dSJefCrHsw6V6XzwIDAQAB"
                SOCIAL_AUTH_KEYCLOAK_AUTHORIZATION_URL= "https://keycloak.tutoric.ucoip.pt/realms/tutoric/protocol/openid-connect/auth"                 # url do tutoric
                SOCIAL_AUTH_KEYCLOAK_ACCESS_TOKEN_URL= "https://keycloak.tutoric.ucoip.pt/realms/tutoric/protocol/openid-connect/token"                 # url do tutoric
                SOCIAL_AUTH_KEYCLOAK_ID_KEY= "email"
                SOCIAL_AUTH_KEYCLOAK_OIDC_VERIFY_SSL = "false"
                SOCIAL_AUTH_OAUTH_SECRETS={ "keycloak": "Zm19jiV9AqeMCHDfvwRlPDEF9eEeCHxz" }
~                                                                                                                                                                                                             
~                                                                                                                                                                                                             
~                                                                                                                                                                                                             
----------------------------------------------------------------------------------------------------------------                                                                                                          

INSTRUÇÕES DE INSTALAÇÃO:

** É necessário que o keycloak tenha certificados válidos:
    '''
    # *************************************************
    # ****** KEYCLOAK.A2O.UCOIP.PT ****
    # *************************************************
    <VirtualHost *:443>
        ServerAdmin administrator@a2o.ucoip.pt
        DocumentRoot /home1/_sites/keycloaka2oucoippt/site/
        ServerName keycloak.a2o.ucoip.pt
        ErrorLog /home1/_sites/keycloaka2oucoippt/log/apache/error.log
        CustomLog /home1/_sites/keycloaka2oucoippt/log/apache/access.log combined
        php_admin_value safe_mode 0
        php_admin_value open_basedir /home1/_sites/keycloaka2oucoippt:/tmp
        AddDefaultCharset On
        UseCanonicalName Off
        SSLEngine on
        SSLCertificateFile    /etc/letsencrypt/live/keycloak.a2o.ucoip.pt/fullchain.pem
        SSLCertificateKeyFile /etc/letsencrypt/live/keycloak.a2o.ucoip.pt/privkey.pem
        HostNameLookups off
        IdentityCheck off
        ProxyRequests off
        SSLProxyEngine On
        <Proxy *>
            Order deny,allow
            Allow from all
        </Proxy>
        ProxyPass / http://localhost:15350/
        ProxyPassReverse / http://localhost:15350/
    </VirtualHost>
    '''

** Quando se utiliza o comando tutor, tem que se usar sempre com o utilizador operator:

** Gere chaves .pem que serão cadastradas no REALM:
    '''
    openssl genrsa -out private.key 2048
    openssl rsa -in private.key -pubout -out public.key
    '''
    * Cadastre a pub.pem no serviço tutor 'keycloak.yml' e dentro do 'import_realm.json'

** Instalação
    '''
    $bash tutor local launch
        Are you configuring a production platform? Type 'n' if you are just testing Tutor on your local computer [Y/n] 
        Your website domain name for students (LMS) [a2o.ucoip.pt] 
        Your website domain name for teachers (CMS) [studio.a2o.ucoip.pt] 
        Your platform name/title [TutorIC] 
        Your public contact email address [ppereira@a2o.ucoip.pt] 
        The default language code for the platform [en] 
        Activate SSL/TLS certificates for HTTPS access? Important note: this will NOT work in a development environment. [Y/n] 
    '''

** Verificar tutor logs
    '''$bash tutor local logs lms --tail=50 -f'''

** Configurações do client no keycloak:
    ''' 
    Client ID: openedx
    Valid redirect URIs: http://{$DOMAIN_NAME}/auth/complete/keycloak/
    Web origins: https://{$DOMAIN_NAME}
    '''

** Configurações do client scopes no keycloak:
    '''
    Acesse o Keycloak Admin Console:
    • Vá para https://keycloak.{$DOMAIN_NAME} > realm {realm_name} > "Clients" > "openedx"
    • Remova o mapper atual e substitua por um fixo.
    • Na aba "Client Scopes" > Crie um novo mapper fixo:
        ◦ Clique em "Create" ou "Add Mapper"
        ◦ Name: audience
        ◦ Mapper Type: Audience (não "Audience Resolve")
        ◦ Included Client Audience: Selecione openedx no dropdown
        ◦ Add to access token: Ative (ON)
        ◦ Add to token introspection: Ative (ON, opcional)
        ◦ Salve as alterações

        ps: Isso força o aud a ser exatamente openedx, evitando valores dinâmicos indesejados.
    '''

** O ficheiro de configuração do tutor está localizado no seguinte diretório:
    '''/home1/_locals/operator/.local/share/tutor/config.yml'''

** Para configurar o keycloak no tutor foi feito um plugin que está localizado no seguinte diretório:
    '''/home1/_locals/operator/.local/share/tutor-plugins/keycloak.yml'''

** É preciso atualizar os dominíos e a SECRET KEY nos ficheiros 'config.yml' & 'keycloak':
    '''Zm19jiV9AqeMCHDfvwRlPDEF9eEeCHxz'''

** Caso seja para se criar uma chave pública dinâmica, será necessário criar um script para passar a chave pública do realm para dentro da variável do keycloak.yml:
    '''SOCIAL_AUTH_KEYCLOAK_PUBLIC_KEY= "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAoS4i5QZfeMLcyXxP2Yc/XtOpa+MwHpOkbQN7zk9LlAm1iG11xbBh60gqNZAXy25NbEhpQWhpG7AnxBT4VSX6zxaIGqzAO+PV8EsJPjvhi5V+m8sU90nTx8abXt+sB5qhw7zEZWjlpRb9H+ago98pPxqn4fkbdspK49QBlTFQ0cXIn6rhkznJqpzrrlDXt5Eclt3i7kyBqRu7APV58+dFB6mfJ6eLHkbdq+nAaGWLKW33FAq5ooVtpjAdKVo0uimjLcd13qe6ERXCr/LjYNfwgVMwirxf/5/15xtw7hpnXj5d+M5MdFLYG/EPBAEijMQBBBhoveVASuAxItTK/LJawwIDAQAB"'''



#########################################################################################################################
##                                                                                                                     ##
##                                                          BUGS                                                       ##
##                                                                                                                     ##
#########################################################################################################################

** Quando se é feito a autenticação em algum serviço usando o keycloak, ele está a gerar má formação no contexto dos
valores relativos as configurações do usuário:
    *bug:
        lms-1  | 2025-02-28 10:27:25,781 INFO 14 [common.djangoapps.third_party_auth.pipeline] [user None] [ip 194.65.131.237] 
        pipeline.py:1032 - [THIRD_PARTY_AUTH] get_username complete: details={'username': 'vlopes', 'email': 'vlopes@a2o.ucoip.pt', 
        'fullname': 'Vinicius Lopes Vinicius Lopes', 'first_name': 'Vinicius Lopes', 'last_name': 'Vinicius Lopes'}, final_username=vlopes
    *resolução:
        Alteração das configurações de importação no serviço keycloak:
        '''
        Keycloak > User federation > first name > LDAP Attribute: 'givenName'
        '''
