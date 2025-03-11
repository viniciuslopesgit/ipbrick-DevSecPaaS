#########################################################################################################################
##                                                                                                                     ##
##                                                                                                                     ##
##                                          A2o + Steptutor v2 - IPBRICK-DEVOPS                                        ##
##                                                                                                                     ##
##                                                                                    IPBRICK by EXPANDINDUSTRIA 2025  ##
#########################################################################################################################

 
** É necessário que o keycloak tenha certificados válidos:	
	sudo apt update
	sudo apt install certbot python3-certbot-apache

	Gerar certificados
	sudo certbot certonly --standalone -d keycloak.tutoric.ucoip.pt

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
