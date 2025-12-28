# 1. Sobrescreve o arquivo README.md com as novas instruções
cat <<EOF > README.md
# 📋 Dockerized Kanban Board

Um sistema de gerenciamento de tarefas estilo Kanban, desenvolvido para consolidar conhecimentos em arquitetura Full Stack baseada em containers.

## 🚀 Primeira Instalação (Setup)
Se você acabou de clonar o projeto, rode estes comandos uma única vez para configurar tudo:

1. **Subir o ambiente:**
   \`\`\`bash
   docker compose up -d
   \`\`\`

2. **Instalar dependências (Backend & Frontend):**
   \`\`\`bash
   docker compose exec backend composer install
   docker compose exec frontend npm install
   \`\`\`

3. **Criar tabelas no Banco:**
   \`\`\`bash
   docker compose exec backend php artisan migrate
   \`\`\`

---

## ⚙️ Comandos do Dia a Dia (Desenvolvimento)

Aqui estão os comandos para iniciar e pausar seu trabalho.

### ▶️ Iniciar o Projeto
Levanta todos os containers (Site, API e Banco) e libera o terminal.
\`\`\`bash
docker compose up -d
\`\`\`
*Acesse em: http://localhost:5173*

### ⏸️ Pausar o Projeto
Para os containers, mas mantém o estado deles (rápido para voltar depois).
\`\`\`bash
docker compose stop
\`\`\`

### ⏹️ Parar Totalmente (Derrubar)
Para e remove os containers e redes (bom para liberar memória do PC).
\`\`\`bash
docker compose down
\`\`\`

---

## 🛠️ Comandos Úteis

**Acessar o terminal do Backend (Laravel):**
\`\`\`bash
docker compose exec backend bash
# Lá dentro você pode rodar: php artisan make:controller, etc.
\`\`\`

**Acessar o terminal do Frontend (Vue):**
\`\`\`bash
docker compose exec frontend sh
# Lá dentro você pode rodar: npm install pacote-novo
\`\`\`

**Ver logs de erro (se algo quebrar):**
\`\`\`bash
docker compose logs -f backend
# ou
docker compose logs -f frontend
\`\`\`

## 💻 Tecnologias
- Docker & Docker Compose
- Laravel 11 (API)
- Vue.js 3 + Vite
- MySQL 8
- Nginx
EOF
