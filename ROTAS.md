# 📑 Documentação Técnica das Rotas

Resumo técnico para integração Frontend <-> Backend.

#### 🌍 Base URLs

* **Frontend (Aplicação):** `http://localhost:5173`
* **Backend (API):** `http://localhost:8000/api`

---

### 🔐 1. Autenticação & User

*Headers necessários:* `Accept: application/json`

| Método | Endpoint | Descrição | Payload (Body) | Retorno |
| --- | --- | --- | --- | --- |
| **POST** | `/register` | Cria nova conta | `name`, `email`, `password`, `password_confirmation` | Token + User |
| **POST** | `/login` | Entra no sistema | `email`, `password` | Token + User |
| **POST** | `/logout` | Sai do sistema (Revoga Token) | N/A *(Requer Token)* | Msg Sucesso |
| **GET** | `/user` | Dados do usuário logado | N/A *(Requer Token)* | Objeto User |

---

### 📋 2. Módulo Kanban

*Headers necessários:* `Authorization: Bearer {token}`

| Método | Endpoint | Descrição | Payload (Body) |
| --- | --- | --- | --- |
| **GET** | `/kanban` | **Rota Principal:** Retorna Colunas, Cards e Subtarefas aninhados. | N/A |
| **POST** | `/columns` | Cria nova coluna | `title` (string) |
| **DELETE** | `/columns/{id}` | Deleta coluna (e seus cards) | N/A |
| **POST** | `/cards` | Cria novo card | `title` (string), `column_id` (int) |
| **PUT** | `/cards/{id}` | Atualiza Card (Move, Edita, %) | `title`, `description`, `percentage`, `column_id`, `order` |
| **POST** | `/subtasks` | Adiciona checklist | `card_id`, `title` |
| **PUT** | `/subtasks/{id}` | Marca/Desmarca item | `is_completed` (boolean) |

---

### 💰 3. Módulo Financeiro

*Headers necessários:* `Authorization: Bearer {token}`

| Método | Endpoint | Descrição | Payload (Body) |
| --- | --- | --- | --- |
| **GET** | `/balance` | Retorna o dashboard | N/A (Retorna `{income, expense, balance}`) |
| **GET** | `/transactions` | Lista histórico | N/A |
| **POST** | `/transactions` | Nova movimentação | `description`, `amount` (float), `type` ('income'/'expense'), `transaction_date` |
| **PUT** | `/transactions/{id}` | Edita movimentação | Mesmos do POST |
| **DELETE** | `/transactions/{id}` | Remove movimentação | N/A |

---

### ☀️ 4. Módulo Daily (Hábitos)

*Headers necessários:* `Authorization: Bearer {token}`

| Método | Endpoint | Descrição | Payload (Body) |
| --- | --- | --- | --- |
| **GET** | `/daily` | Lista tarefas do dia | N/A |
| **POST** | `/daily` | Cria tarefa rápida | `title`, `is_recurring` (boolean) |
| **PUT** | `/daily/{id}` | Atualiza status/título | `title`, `is_completed` (bool), `is_recurring` (bool) |
| **DELETE** | `/daily/{id}` | Remove tarefa | N/A |
| **POST** | `/daily/reset` | **Iniciar Novo Dia:** Limpa concluídos e reseta hábitos | N/A |

---

### 🖥️ Rotas da Aplicação (Frontend Vue)

Estas são as rotas configuradas no `vue-router`.

| Caminho | Nome | Acesso | Descrição |
| --- | --- | --- | --- |
| `/login` | `login` | Público | Login Split-Screen |
| `/register` | `register` | Público | Cadastro de Usuário |
| `/` | `home` | **Privado** | Dashboard Central (Hero Header) |
| `/kanban` | `kanban` | **Privado** | Quadro de Projetos |
| `/finance` | `finance` | **Privado** | Gestão Financeira |
| `/daily` | `daily` | **Privado** | Lista de Hábitos e Foco Diário |