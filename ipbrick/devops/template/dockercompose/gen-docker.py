import yaml
import json
import os

# 1. Definir os valores de configuração
config = {
    "---SERVER-NAME---": "keyvalue",
    "---LDAP-IP---": "62.28.182.145",
    "---LDAP-BIND---": "a3fe4a032f7d7a7edd1032518c2c6f89"
}

# 2. Função para substituir placeholders em texto
def replace_placeholders(text, replacements):
    for key, value in replacements.items():
        text = text.replace(key, value)
    return text

# 3. Processar o docker-compose template
def process_docker_compose():
    # Ler o template
    with open('docker-compose_template.yml', 'r') as f:
        template_content = f.read()
    
    # Substituir os placeholders
    processed_content = replace_placeholders(template_content, config)
    
    # Converter para formato YAML válido (carregar e salvar para garantir formatação)
    yaml_content = yaml.safe_load(processed_content)
    
    # Salvar o novo docker-compose.yml
    with open('docker-compose.yml', 'w') as f:
        yaml.dump(yaml_content, f, default_flow_style=False, sort_keys=False)

# 4. Processar o template JSON do Keycloak
def process_keycloak_template():
    # Criar o diretório se não existir
    os.makedirs('keycloak/imports', exist_ok=True)
    
    # Verificar se o template existe
    template_path = 'keycloak/imports/template.json'
    if not os.path.exists(template_path):
        print(f"Arquivo {template_path} não encontrado! Criando um template básico...")
        # Criar um template básico se não existir
        basic_template = {
            "realm": "---SERVER-NAME---",
            "enabled": True,
            "ldap": {
                "host": "---LDAP-IP---",
                "bindCredential": "---LDAP-BIND---"
            }
        }
        with open(template_path, 'w') as f:
            json.dump(basic_template, f, indent=2)
    
    # Ler o template
    with open(template_path, 'r') as f:
        template_content = f.read()
    
    # Substituir os placeholders
    processed_content = replace_placeholders(template_content, config)
    
    # Converter para JSON válido
    json_content = json.loads(processed_content)
    
    # Salvar o novo ipbrick_realm.json
    with open('keycloak/imports/ipbrick_realm.json', 'w') as f:
        json.dump(json_content, f, indent=2)

# 5. Executar o processamento
def main():
    try:
        print("Processando docker-compose...")
        process_docker_compose()
        print("Docker-compose.yml gerado com sucesso!")
        
        print("Processando template do Keycloak...")
        process_keycloak_template()
        print("ipbrick_realm.json gerado com sucesso!")
        
    except Exception as e:
        print(f"Erro durante o processamento: {str(e)}")

if __name__ == "__main__":
    main()