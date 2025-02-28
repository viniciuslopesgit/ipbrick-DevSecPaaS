# Escolha a imagem base Debian
FROM debian:bullseye-slim

# Defina variáveis de ambiente para o Keycloak
ENV KEYCLOAK_VERSION=26.0.7
ENV KEYCLOAK_HOME=/opt/keycloak
ENV KEYCLOAK_USER=keycloak
ENV KEYCLOAK_GROUP=keycloak

# Atualize o sistema e instale dependências
RUN apt-get update && \
    apt-get install -y \
    openjdk-17-jdk \
    wget \
    bash \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Baixe o Keycloak (se necessário, altere a URL ou versão)
RUN wget https://github.com/keycloak/keycloak/releases/download/${KEYCLOAK_VERSION}/keycloak-${KEYCLOAK_VERSION}.tar.gz && \
    tar -xvzf keycloak-${KEYCLOAK_VERSION}.tar.gz -C /opt && \
    mv /opt/keycloak-${KEYCLOAK_VERSION} ${KEYCLOAK_HOME} && \
    rm keycloak-${KEYCLOAK_VERSION}.tar.gz

# Defina permissões adequadas para o diretório
RUN groupadd -r ${KEYCLOAK_GROUP} && \
    useradd -r -g ${KEYCLOAK_GROUP} -m ${KEYCLOAK_USER} && \
    chown -R ${KEYCLOAK_USER}:${KEYCLOAK_GROUP} ${KEYCLOAK_HOME}

# Exponha a porta do Keycloak
EXPOSE 8080

# Defina o comando para iniciar o Keycloak
USER ${KEYCLOAK_USER}
CMD ["bash", "-c", "${KEYCLOAK_HOME}/bin/kc.sh start-dev"]

