#!/bin/bash

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"

echo "Diretório raiz detectado: '$PROJECT_ROOT'"

if [ -f "$PROJECT_ROOT/.env" ]; then
    DB_ROOT_PASSWORD=$(grep "^DB_ROOT_PASSWORD=" "$PROJECT_ROOT/.env" | cut -d '=' -f2-)
else
    echo "ERRO: Arquivo .env não encontrado em $PROJECT_ROOT"
    exit 1
fi

BACKUP_DIR="$PROJECT_ROOT/database/backup"
mkdir -p "$BACKUP_DIR"

BACKUP_PATH="$BACKUP_DIR/all-databases_$(date +\%F).sql"

docker exec -i mysql_container mysqldump --all-databases -uroot -p"$DB_ROOT_PASSWORD" > "$BACKUP_PATH"

# Para colocar o script no crontab
# Cole no terminal:
# chmod +x /caminho/para/seu/backup-mysql.sh
# sudo chown -R aluno:aluno /home/aluno/projeto-final-docker-curso-senac/database
# crontab -e
# 0 0 * * * /home/user/projeto-final-docker-curso-senac/database/script/backup-script-mysql.sh