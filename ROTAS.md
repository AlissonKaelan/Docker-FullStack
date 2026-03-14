### 2. 📄 Arquivo `ROTAS.md`

```markdown
# 📑 Documentação Técnica das Rotas

Resumo técnico para integração Frontend <-> Backend.

#### 🌍 Base URLs
* **Frontend (Aplicação):** `http://<SEU_IP>:5173`
* **Backend (API):** `http://<SEU_IP>:8000/api`

---

### 🔐 1. Autenticação & User
*Headers necessários:* `Accept: application/json`

| Método | Endpoint | Descrição | Payload (Body) |
| :--- | :--- | :--- | :--- |
| **GET** | `/sanctum/csrf-cookie`| Inicializa cookie de segurança | N/A *(Chamar fora do prefixo /api)* |
| **POST** | `/register` | Cria nova conta | `name`, `email`, `password`, `password_confirmation` |
| **POST** | `/login` | Entra no sistema | `email`, `password` |
| **POST** | `/logout` | Sai do sistema | N/A *(Requer Token)* |
| **GET** | `/user` | Dados do usuário | N/A *(Requer Token)* |

---

### 📋 2. Módulo Kanban
*Headers necessários:* `Authorization: Bearer {token}`

| Método | Endpoint | Descrição | Payload (Body) |
| :--- | :--- | :--- | :--- |
| **GET** | `/kanban` | **Rota Principal:** Retorna Colunas, Cards e Subtarefas. | N/A |
| **POST** | `/columns` | Cria nova coluna | `title` (string) |
| **DELETE**| `/columns/{id}`| Deleta coluna (e seus cards) | N/A |
| **POST** | `/cards` | Cria novo card | `title` (string), `column_id` (int) |
| **PUT** | `/cards/{id}` | Atualiza Card (Move, Edita, %) | `title`, `description`, `percentage`, `column_id`, `order` |
| **DELETE**| `/cards/{id}` | Remove um card | N/A |
| **POST** | `/subtasks` | Adiciona checklist | `card_id`, `title` |
| **PUT** | `/subtasks/{id}`| Edita Status ou Título | `is_completed` (boolean), `title` (string) |
| **DELETE**| `/subtasks/{id}`| Remove uma subtarefa | N/A |
| **GET** | `/cards/{id}/transactions`| Lista custos vinculados ao Card | N/A |

---

### 💰 3. Módulo Financeiro
*Headers necessários:* `Authorization: Bearer {token}`

#### Transações
| Método | Endpoint | Descrição | Payload (Body) |
| :--- | :--- | :--- | :--- |
| **GET** | `/balance` | Dashboard Financeiro | N/A (Retorna `{income, expense, balance}`) |
| **GET** | `/transactions` | Lista histórico (filtros opcionais) | `?month=X&year=Y` |
| **POST** | `/transactions` | Nova movimentação | `description`, `amount`, `type` ('income'/'expense'), `date`, `category_id`, `installments`, **`card_id` (opcional)** |
| **PUT** | `/transactions/{id}`| Edita movimentação | Mesmos do POST + `update_all` (bool para parcelas) |
| **DELETE**| `/transactions/{id}`| Remove movimentação | `?delete_all=true` (opcional para parcelas) |

#### Categorias
| Método | Endpoint | Descrição | Payload (Body) |
| :--- | :--- | :--- | :--- |
| **GET** | `/categories` | Lista todas as categorias | N/A |
| **POST** | `/categories` | Cria categoria | `name`, `color` (hex), `type` ('income'/'expense') |
| **DELETE**| `/categories/{id}`| Apaga categoria | N/A |

---

### ☀️ 4. Módulo Daily (Hábitos)
*Headers necessários:* `Authorization: Bearer {token}`

| Método | Endpoint | Descrição | Payload (Body) |
| :--- | :--- | :--- | :--- |
| **GET** | `/daily` | Lista tarefas do dia | N/A |
| **POST** | `/daily` | Cria tarefa rápida | `title`, `is_recurring` (boolean) |
| **PUT** | `/daily/{id}` | Atualiza status/título | `title`, `is_completed` (bool), `is_recurring` (bool) |
| **DELETE**| `/daily/{id}` | Remove tarefa | N/A |
| **POST** | `/daily/reset` | **Iniciar Novo Dia:** Limpa concluídos e reseta hábitos | N/A |