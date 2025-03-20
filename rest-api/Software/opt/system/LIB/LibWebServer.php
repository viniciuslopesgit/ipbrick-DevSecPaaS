<?php
#########################################################################################################################
##                                                                                                                     ##
##                                                    IPBRICK-OS                                                       ##
##                                                                                                                     ##
##                                                                                                                     ##
##  REST API - LibWebServer                                                           IPBRICK by EXPANDINDUSTRIA 2025  ##
#########################################################################################################################
function getWebServerInfoByIdapache($idapache)
{
    global $dbapache;

    $info = $dbapache->getApacheByIdapache($idapache);
    return $info;
}

function addWebSite($websiteargs, &$error)
{
    global $dbservidor, $dbapache, $dbftp, $dbnamed_conf, $dbdns_in_a, $dbdns_cname;
    global $array_system_users;

    logDebug("  -> Iniciando addWebSite ...");
    logDebug("      -> Argumentos recebidos:");
    if (isset($websiteargs->servername)) logDebug("          {");
    if (isset($websiteargs->servername)) logDebug("          servername: $websiteargs->servername");
    if (isset($websiteargs->servername)) logDebug("          serveralias: $websiteargs->serveralias");
    if (isset($websiteargs->servername)) logDebug("          serveradmin: $websiteargs->serveradmin");
    if (isset($websiteargs->servername)) logDebug("          ftplogin: $websiteargs->ftplogin");
    if (isset($websiteargs->servername)) logDebug("          ftppass: $websiteargs->ftppass");
    if (isset($websiteargs->servername)) logDebug("          documentroot: $websiteargs->documentroot");
    if (isset($websiteargs->servername)) logDebug("          internet: $websiteargs->internet");
    if (isset($websiteargs->servername)) logDebug("          safe_mode: $websiteargs->safe_mode");
    if (isset($websiteargs->servername)) logDebug("          open_basedir: $websiteargs->open_basedir");
    if (isset($websiteargs->servername)) logDebug("          }");

    $error = [];
    $servidor = $dbservidor->getServidor();

    // 1. TEST PROTOCOL [URL address Protocol]
    logDebug("      -> Verificando argumentos recebidos...");
    if (isset($websiteargs->protocol))
    {
        $protocol = $websiteargs->protocol;
        if (is_numeric($protocol) && ($protocol === 1 || $protocol === 2 || $protocol === 3))
            logDebug("          1.[OK]    protocol");
        else
            logDebug("          1.[FAIL]    Error! 3045");
    }
    else
    {
        //Defaul value is 1 (HTTP)
        logDebug("          1. Definido como HTTP");
        $protocol = 1;
    }
    
    // 2. TEST SERVERNAME [URL address]
    if (isset($websiteargs->servername))
    {
        $servername = mb_strtolower(trim($websiteargs->servername));
        if (@!$dbapache->testa_servername($servername))
            logDebug("  1.[FAIL]    Error! 3046");
        $teste3 = $dbapache->getApacheByServername($servername);
        $teste8 = $dbapache->getApacheByServeralias($servername);
        if (count((array) $teste3) || count((array) $teste8))
            logDebug("          2.[FAIL]    Error! 3047");
    }
    else
        logDebug("          2.[FAIL]    Error! 3046");
    logDebug("          2.[OK]    Servername");

    // 3. TEST SERVERALIAS [Alternative URL addresses:]
    if (isset($websiteargs->serveralias))
    {
        $srvalias = $websiteargs->serveralias;
        $count_srvalias = count((array) $srvalias);
        for ($i = 0; $i < $count_srvalias; $i++)
        {
            $test_serveralias = mb_strtolower(trim($srvalias[$i]));
            if (@$dbapache->testa_serveralias($test_serveralias))
            {
                if ($serveralias == "")
                    $serveralias = $test_serveralias;
                else
                    $serveralias .= " " . $test_serveralias;  // Espaco ao invés de vírgula
            }
            else
                logDebug("          3.[FAIL]    Error! 3048");

            $teste7 = $dbapache->getApacheByServeralias($test_serveralias);
            $teste9 = $dbapache->getApacheByServername($test_serveralias);
            if (count((array) $teste7) || count((array) $teste9))
                logDebug("          3.[FAIL]    Error! 3047");
        }
    }
    else
        $serveralias = "";
    logDebug("          3.[OK]    serveralias");

    // 4. TEST SERVERADMIN [Site administrator email]
    if (isset($websiteargs->serveradmin))
    {
        $serveradmin = mb_strtolower(trim($websiteargs->serveradmin));
        if ($serveradmin == '')
            $serveradmin = 'administrator@' . $servidor[0]->dominio;
    }
    else
        $serveradmin = 'administrator@' . $servidor[0]->dominio;
    logDebug("          4.[OK]    serveradmin");

    // 5. TEST FTPLOGIN [FTP User]
    if (isset($websiteargs->ftplogin))
    {
        $ftplogin = mb_strtolower(trim($websiteargs->ftplogin));
        $teste6 = $dbftp->getFTPAccountsByFtplogin($ftplogin);
        if (count((array) $teste6))
            logDebug("  5.[FAIL]    Error! 3049");
        //testa ftplogin nos utilizadores do sistema
        $teste_usersys = getSystemUserInfoByLogin($ftplogin);
        if (count((array) $teste_usersys))
            logDebug("  5.[FAIL]    Error! 3050");
        if (in_array($ftplogin, $array_system_users))
            logDebug("  5.[FAIL]    Error! 3051");
        if (@!$dbftp->testa_ftplogin($ftplogin))
            logDebug("  4.[FAIL]    Error! 3051");
    }
    else
        logDebug("          5.[FAIL]    Error! 3052");
    logDebug("          5.[OK]    ftplogin");

    // 6. TEST FTPPASS [Password]
    if (isset($websiteargs->ftppass))
    {
        $ftppass = mb_strtolower(trim($websiteargs->ftppass));
        if (mb_strlen($ftppass) < 6)
            logDebug("      6.[FAIL]    Error!  1157"); //The password does not meet security policies
    }
    logDebug("          6.[OK]    ftppass");

    // 7. TEST DOCUMENTROOT [Site folder location]
    if (isset($websiteargs->documentroot))
    {
        $documentroot = mb_strtolower(trim($websiteargs->documentroot));
        if (@!$dbapache->testa_documentroot($documentroot)) 
            logDebug("          7.[FAIL]    Error! 3053");
        $documentroot = "/home1/_sites/" . $documentroot . "/";
        $teste4 = $dbapache->getApacheByDocRoot($documentroot);
        if (count((array) $teste4))
            logDebug("          7.[FAIL]    Error! 3054");
        if ($documentroot == '')
            logDebug("          7.[FAIL]    Error! 3053");
    }
    else
        logDebug("          7.[FAIL]    Error! 3053");
    logDebug("          7.[OK]    documentroot");

    // 8. TEST INTERNET [Internet Availability]
    if (isset($websiteargs->Internet))
    {
        $internet = $websiteargs->Internet;
        if (is_numeric($internet) && ($internet === 0 || $internet === 1))
            logDebug("          8.[OK]    internet");
        else
            logDebug("          8.[FAIL]    Error! 1001");
    }
    else
        $internet = 1;
    logDebug("          8.[OK]    internet");

    // 9. TEST SAFE_MODE [Safe mode]
    if (isset($websiteargs->safe_mode))
    {
        $safemode = $websiteargs->safe_mode;
        if (is_numeric($safemode) && ($safemode === 0 || $safemode === 1))
        {
            $phpsafemode = "php_admin_value safe_mode " . $safemode;
            logDebug("          9.[OK]    safe_mode");
        }
        else
            logDebug("          9.[FAIL]    Error! 1001");
    }
    else
        $phpsafemode = "php_admin_value safe_mode 0";

    // 10. TEST OPEN_BASEDIR [Access authorized only to the directories]
    if (isset($websiteargs->open_basedir))
    {
        $open_basedir = array_filter(explode('/', $websiteargs->open_basedir));
        $error_open_basedir = 0;
        $count_open_basedir = count($open_basedir);
        for ($i = 0; $i < $count_open_basedir; $i++)
        {
            // Ajuste na expressão regular
            if (preg_match('/^\/([\w-]+(?:\/[\w-]+)*)$/', '/' . $open_basedir[$i]))
                logDebug("          10.[OK]    open_basedir parte válida: " . $open_basedir[$i]);
            else
            {
                $error_open_basedir = 1;
                logDebug("          10.[FAIL]    Erro! 3055");
            }
        }
        if ($error_open_basedir == 0)
        {
            $phpopenbasedir = implode(":", $open_basedir);
            $phpopenbasedir = "php_admin_value open_basedir " . $phpopenbasedir;
        }
    }
    else
        $phpopenbasedir = "php_admin_value open_basedir none";
    logDebug("          10.[OK]    open_base");


    // 11. TEST CHARSET [Character encoding]
    if (isset($websiteargs->charset))
    {
        $charset = trim($websiteargs->charset);
        if (is_string($charset))
            $DefaultCharset = "AddDefaultCharset " . $charset;
        else
            logDebug("          11.[FAIL]    Erro! 3056");
    }
    else
        $DefaultCharset = "AddDefaultCharset On";
    logDebug("          11.[OK]    charset");

    // 12. TESTE CANNONOCAL NAME [Always keep the typed URL]
    if (isset($websiteargs->canonicalname))
    {
        $canonicalname = trim($websiteargs->canonicalname);
        if (is_string($canonicalname))
            $UseCanonicalName = "UseCanonicalName " . $canonicalname;
        else
            logDebug("          12.[FAIL]    Erro! 1001");
    }
    else
        $UseCanonicalName = "UseCanonicalName Off";

    $websitetype = 2;
    $documentrootfull = $documentroot . "site/";
    $errorlog = $documentroot . "log/apache/error.log";
    $transferlog = $documentroot . "log/apache/access.log";

    if (count($error) == 0)
    {
        // Insere conta FTP
        $idapache = $dbapache->insertApache($servername, $serveradmin, $documentroot, $internet, $websitetype, $documentrootfull, $errorlog, $transferlog, $protocol, $serveralias);
        $dbapache->insertApacheOtherOpt($idapache, $phpsafemode, 1);
        $dbapache->insertApacheOtherOpt($idapache, $phpopenbasedir, 2);
        $dbapache->insertApacheOtherOpt($idapache, $DefaultCharset, 4);
        $dbapache->insertApacheOtherOpt($idapache, $UseCanonicalName, 5);

        // Insert cname into dns (servername)
        $pos = mb_strpos($servername, ".");
        if ($pos !== false)
        {
            $name_servername = mb_substr($servername, 0, $pos);
            $domain_servername = mb_substr($servername, $pos + 1);
            $nome_servidor_real = $servidor[0]->nome;
            $zonas = $dbnamed_conf->getNamed_confByZonaInverseType($domain_servername, 'f', 'master');
            $n_zonas = count((array) $zonas);

            for ($j = 0; $j < $n_zonas; $j++)
            {
                if ($zonas[$j]->zona == $domain_servername)
                {
                    $dnsina = $dbdns_in_a->getDns_in_aByNomeIdzona($nome_servidor_real, $zonas[$j]->idzona);
                    if ($dnsina[0]->nome == $nome_servidor_real)
                    {
                        $dns_in_a = $dbdns_in_a->getDns_in_aByNomeIdzona($name_servername, $zonas[$j]->idzona);
                        $n_dns_in_a = count((array) $dns_in_a);
                        $cnames = $dbdns_cname->getDns_cnameByCnameIdzona($name_servername, $zonas[$j]->idzona);
                        $n_cnames = count((array) $cnames);
                        if ($n_cnames == 0 && $n_dns_in_a == 0)
                        {
                            $iddns_cname = $dbdns_cname->getMaxIddns_cname();
                            $dbdns_cname->insertDns_cname($iddns_cname, $zonas[$j]->idzona, $dnsina[0]->iddns_in_a, "", $name_servername);
                        }
                    }
                }
            }
        }
        // Insert cname into dns (serveralias)
        $srvalias = explode(",", $serveralias);
        for ($i = 0; $i < count((array) $srvalias); $i++)
        {
            if ($srvalias[$i] != "")
            {
                $pos = mb_strpos($srvalias[$i], ".");
                if ($pos !== false) {
                    $name_servername = mb_substr($srvalias[$i], 0, $pos);
                    $domain_servername = mb_substr($srvalias[$i], $pos + 1);
                    $nome_servidor_real = $servidor[0]->nome;
                    $zonas = $dbnamed_conf->getNamed_confByZonaInverseType($domain_servername, 'f', 'master');
                    $n_zonas = count((array) $zonas);

                    for ($k = 0; $k < $n_zonas; $k++)
                    {
                        if ($zonas[$k]->zona == $domain_servername)
                        {
                            $dnsina = $dbdns_in_a->getDns_in_aByNomeIdzona($nome_servidor_real, $zonas[$k]->idzona);
                            if ($dnsina[0]->nome == $nome_servidor_real)
                            {
                                $dns_in_a = $dbdns_in_a->getDns_in_aByNomeIdzona($name_servername, $zonas[$k]->idzona);
                                $n_dns_in_a = count((array) $dns_in_a);
                                $cnames = $dbdns_cname->getDns_cnameByCnameIdzona($name_servername, $zonas[$k]->idzona);
                                $n_cnames = count((array) $cnames);
                                if ($n_cnames == 0 && $n_dns_in_a == 0)
                                {
                                    $iddns_cname = $dbdns_cname->getMaxIddns_cname();
                                    $dbdns_cname->insertDns_cname($iddns_cname, $zonas[$k]->idzona, $dnsina[0]->iddns_in_a, "", $name_servername);
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

function addReverseProxy($reverseproxyargs, &$error)
{

    global $dbservidor, $dbapache, $dbapachealias;
    global $array_system_users;

    $error = array();
    $idapache = 0; // É preciso iniciar a variável para que a função crie um id automaticamente; ex: 200-201...
    // 1. TESTE ALIAS [Proxy Alias]
    if (isset($reverseproxyargs->alias))
    {
        $alias = mb_strtolower(trim($reverseproxyargs->alias));
        if (!empty($alias))
        {
            if (preg_match('/^(\/[a-zA-Z0-9\-\._~%!$&\'()*+,;=:@]+)*\/?$/', $alias) === 1)
            {
                //value is OK
                //Removes "/" at the beginning of the Alias
                if ($alias[0] === '/')
                    $alias = substr($alias, 1);
                else
                    $error[] = 3057;
            }
            else
                logDebug("value is OK (vazio)");
        }
    }
    else
        $alias = ""; //Default pode ser vazio

    // 2. TEST URL [URL]
    if (isset($reverseproxyargs->host))
    {
        $host = mb_strtolower(trim($reverseproxyargs->host));
        if (WebServerIsValidURL($host))
        {
            logDebug("value is OK");
            if (($alias == '') && ($host[mb_strlen($host) - 1] != '/'))
                $host = trim($host) . '/';
        } 
        else
            $error[] = 3046;
    }
    else
        $error[] = 3046; //Default não pode ser vazio

    // 3. TEST USE-LOCATION  [Enable Location directive]
    if (isset($reverseproxyargs->uselocation))
    {
        $uselocation = mb_strtolower(trim($reverseproxyargs->uselocation));
        if ((is_string($uselocation) && strlen($uselocation) === 1) && ($uselocation == 't' || $uselocation == 'f'))
            logDebug("value is OK");               
        else
            $error[] = 1001;
    }
    else
        $uselocation = 'f'; // Default é FALSE

    // 4. TESTE HTTPCOMP [Enable compatibility with HTTP/1.0 protocol]
    if (isset($websiteargs->httpcomp))
    {
        $httpcomp = mb_strtolower(trim($reverseproxyargs->httpcomp));
        if (in_array($httpcomp, ['t', 'f'], true))
            logDebug("value is OK");
        else
            $error[] = 1001;
    }
    else
        $httpcomp = 'f'; //Defaul value is 'f'

    // 5. TEST SELFSIGNEDCOMP [Enable compatibility with self-signed certificate]
    if (isset($websiteargs->selfsignedcomp))
    {
        $selfsignedcomp = mb_strtolower(trim($reverseproxyargs->selfsignedcomp));
        if (in_array($selfsignedcomp, ['t', 'f'], true))
            logDebug("value is OK");
        else
            $error[] = 1001;
    }
    else
        $selfsignedcomp = 'f'; //Defaul value is 'f'
    if (count($error) == 0) {
        $internet = 0;
        $dbapachealias->insertApacheReverseProxy($idapache, $alias, $host, $internet, $httpcomp, $selfsignedcomp, $uselocation);
    }
}

function WebServerIsValidURL($url)
{
    if (!preg_match('/^https?:\/\//', $url)) // Add HTTP if missing
        $url = 'http://' . $url;
    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}