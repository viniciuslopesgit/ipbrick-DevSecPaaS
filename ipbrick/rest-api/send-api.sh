#!/bin/bash
#
# Requiriments: apt install inotify-tools;  // monitoriza eventos nos ficheiros
#               
#
echo "
/////////////////////////////////////////////////////////////////////////////
////                                                                     ////
////                    APIREST - LibRESTWebServer.php                   ////
////                                                                     ////
/////////////////////////////////////////////////////////////////////////////
"
echo "### Monitorizando ficheiros ### ..."
# Verifica erros
check_error()
{
    if [ $? -ne 0 ]; then
        echo "Erro: $1"
        exit 1
    fi
}

upload_file()
{
    local source_file="$1"
    local dest_path="$2"
    echo "Alteração detectada em $source_file. Enviando para $dest_path..."
    sshpass -p 'QoJv96V8RjLd' scp "$source_file" "operator@172.18.203.225:$dest_path"
    check_error "Erro ao enviar $source_file para $dest_path"
}

declare -A file_mappings
file_mappings=(
    ["client/getUsers.php"]="/tmp/api-client/getUsers.php"
    ["client/addWebSite.php"]="/tmp/api-client/addWebSite.php"
    ["Software/opt/system/site/api/server.php"]="/opt/system/site/api/server.php"
    ["Software/opt/system/LIB/webservice/LibWSRestUsers.php"]="/opt/system/LIB/webservice/LibWSRestUsers.php"
    ["Software/opt/system/LIB/webservice/LibRESTWebServer.php"]="/opt/system/LIB/webservice/LibRESTWebServer.php"
    ["Software/opt/system/LIB/LibWebServer.php"]="/opt/system/LIB/LibWebServer.php"
)
echo "Fazendo upload inicial dos ficheiros..."
for source in "${!file_mappings[@]}"; do
    upload_file "$source" "${file_mappings[$source]}"
done
echo "Upload inicial concluído.";
echo "Iniciando monitorização...";

# Monitorizar alterações nos diretórios
inotifywait -m -r -e modify,create,delete "client" "Software" |
while read -r directory event file; do
    # Construir o caminho completo do ficheiro alterado
    full_path="$directory$file"

    # Verificar se o ficheiro alterado está no mapeamento
    if [[ -n "${file_mappings[$full_path]}" ]]; then
        upload_file "$full_path" "${file_mappings[$full_path]}"
    fi
done
# Isto só aparece se o inotifywait falhar
echo "Monitorização terminada."
