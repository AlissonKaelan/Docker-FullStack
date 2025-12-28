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



# Fase 1: Configuração de Infraestrutura (Docker)

## Visão Geral
Nesta etapa, foi criado o ambiente de desenvolvimento conteinerizado utilizando Docker e Docker Compose, eliminando a necessidade de instalar PHP/Node/MySQL diretamente no host. A arquitetura segue o padrão de containers isolados (Microserviços).

## Arquitetura dos Containers
- **App Backend (app_backend):** Container PHP 8.2-FPM com extensões necessárias para Laravel (GD, BCMath, PDO MySQL).
- **App Frontend (app_frontend):** Container Node.js (Alpine) rodando Vite server para Vue.js 3.
- **Database (app_db):** MySQL 8.0 com persistência de dados via volumes Docker.
- **Webserver (app_server):** Nginx (Alpine) atuando como Reverse Proxy. Redireciona tráfego da porta 8000 para a API (PHP-FPM).
- **Gerenciador DB (app_pma):** PhpMyAdmin rodando na porta 8081 para administração visual do banco.

## Portas Mapeadas (Host -> Container)
| Serviço | Porta Host | Porta Interna | Descrição |
| :--- | :--- | :--- | :--- |
| API (Nginx) | `8000` | `80` | Ponto de entrada da aplicação Backend |
| Frontend | `5173` | `5173` | Servidor de desenvolvimento Vue (Hot Reload) |
| PhpMyAdmin | `8081` | `80` | Interface do Banco de Dados |
| MySQL | `N/A` | `3306` | Acessível apenas internamente pela rede `app-net` |

## Comandos Principais
- Iniciar ambiente: `docker compose up -d`
- Parar ambiente: `docker compose down`
- Acessar container PHP: `docker compose exec backend bash`