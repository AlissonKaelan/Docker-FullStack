#!/bin/bash

# 1. Configurações
BACKUP_DIR="/home/alisson/Docker-FullStack/backups"
DATA_ATUAL=$(date +"%Y-%m-%d_%H-%M")
NOME_ARQUIVO="backup_producao_$DATA_ATUAL.sql"

# 2. Executa o backup (Usamos o caminho absoluto do docker por segurança no cron)
/usr/bin/docker exec app_db mariadb-dump -u root -p'secret' kanban_db > $BACKUP_DIR/$NOME_ARQUIVO

# 3. Limpeza Inteligente: Apaga backups com mais de 30 dias para não lotar o HD do servidor!
find $BACKUP_DIR -type f -name "*.sql" -mtime +30 -exec rm {} \;

echo "Backup $NOME_ARQUIVO concluído com sucesso!"
