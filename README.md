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


## Fase 2 e 3: Banco de Dados e API REST

### Modelagem de Dados
- **Tabela `columns`:** Representa as listas do Kanban (To Do, Doing, Done). Possui campo `order_index` para ordenação visual.
- **Tabela `cards`:** Representa as tarefas. Possui chave estrangeira `column_id` ligando à coluna e `order_index` para posição.
- **Relacionamento:** Implementado `One-to-Many` (Uma Coluna tem N Cards).

### API Endpoints
| Método | Rota | Controller | Descrição |
| :--- | :--- | :--- | :--- |
| `GET` | `/api/kanban` | `KanbanController@index` | Retorna todas as colunas com seus respectivos cards (Eager Loading) |
| `POST` | `/api/cards` | `KanbanController@storeCard` | Cria um novo cartão |
| `PUT` | `/api/cards/{id}` | `KanbanController@updateCard` | Move o cartão entre colunas ou muda posição |

### Soluções Técnicas
- Utilizado **Eager Loading** (`with('cards')`) para otimizar consultas SQL (N+1 Problem).
- Criado **Seeder** para popular o banco com dados iniciais para testes de frontend.


## Fase 4: Frontend Vue.js e Interatividade

### Tecnologias
- **Vue 3 (Composition API):** Gerenciamento de estado reativo.
- **Axios:** Cliente HTTP para comunicação assíncrona com a API Laravel.
- **VueDraggable:** Biblioteca baseada no `Sortable.js` para funcionalidade Drag & Drop.

### Implementação do Drag & Drop
A lógica de persistência foi implementada no evento `@change` do componente draggable:
1.  **Detecção:** O evento identifica se o cartão foi *adicionado* a uma nova coluna ou *movido* na mesma.
2.  **Payload:** Captura o `id` do cartão, o `id` da nova coluna e o novo `order_index`.
3.  **Persistência:** Dispara requisição `PUT /api/cards/{id}`.
    - O Backend valida os dados.
    - O banco de dados é atualizado.
    - Em caso de erro na API, seria necessário reverter o estado visual (rollback), mas o MVP assume sucesso.

### Estrutura de Componentes
- `KanbanBoard.vue`: Componente inteligente que busca os dados (`onMounted`) e gerencia a lógica de movimentação.