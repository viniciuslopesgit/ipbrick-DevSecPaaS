<?php
function export_from_json_template() {
    global $_path;
    global $_path_gerados;

    // Log do início do processo
    IpbLogMessage("Generate new file from import_template.json\n");

    // Carrega o template base (ajuste o caminho conforme necessário)
    $template = "\n## Generated at " . date("Y-M-d H:m") . "\n\n";
    $template .= file_get_contents("/opt/system/include.d/include/devops/import_template.json");

    // Carrega o arquivo JSON com as variáveis
    $json_file = file_get_contents("/opt/system/include.d/include/devops/import_template.json");
    if ($json_file === false) {
        IpbLogMessage("Error: Unable to load import_template.json\n");
        return 0;
    }

    // Decodifica o JSON para um array associativo
    $variables = json_decode($json_file, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        IpbLogMessage("Error: Invalid JSON format in import_template.json - " . json_last_error_msg() . "\n");
        return 0;
    }

    // Substitui as variáveis no template
    foreach ($variables as $key => $value) {
        $placeholder = "---" . strtoupper($key) . "---";
        $template = str_replace($placeholder, $value, $template);
    }

    // Define o caminho completo para o arquivo de saída
    $output_dir = "/opt/devops/docker-compose/keycloak/imports";
    $output_file = $output_dir . "/output_from_json_template.yml";

    // Verifica se o diretório existe, cria se necessário
    if (!is_dir($output_dir)) {
        if (!mkdir($output_dir, 0755, true)) {
            IpbLogMessage("Error: Unable to create directory $output_dir\n");
            return 0;
        }
    }

    // Salva o arquivo gerado
    if (file_put_contents($output_file, $template) === false) {
        IpbLogMessage("Error: Unable to write to $output_file\n");
        return 0;
    }

    IpbLogMessage("Successfully generated $output_file\n");
    return 1;
}