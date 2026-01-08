### 📑 Documentação das Rotas

Aqui está o resumo técnico que você pediu para adicionar à sua documentação. Isso é exatamente o que um desenvolvedor Front-end precisaria para consumir sua API.

#### 🌍 Base URLs

* **Frontend (Aplicação):** `http://localhost:5173`
* **Backend (API):** `http://localhost:8000/api`

#### 🔐 Rotas da API (Backend)

*Todas as rotas abaixo exigem Header:* `Authorization: Bearer {token}`

| Método | Endpoint | Descrição | Parâmetros Esperados (Body) | Retorno |
| --- | --- | --- | --- | --- |
| **GET** | `/transactions` | Lista o histórico do usuário | N/A | Array de objetos `Transaction` |
| **POST** | `/transactions` | Cria nova transação | `description` (string), `amount` (decimal), `type` ('income'/'expense'), `transaction_date` (Y-m-d) | Objeto `Transaction` criado |
| **GET** | `/balance` | Retorna totais e saldo | N/A | `{ "income": 100, "expense": 50, "balance": 50 }` |

#### 🖥️ Rotas da Aplicação (Frontend)

| Caminho | Nome (Vue Router) | Protegida? | Descrição |
| --- | --- | --- | --- |
| `/login` | `login` | Não | Tela de Login e acesso ao token |
| `/kanban` | `kanban` | **Sim** | Quadro de tarefas (Drag & Drop) |
| `/finance` | `finance` | **Sim** | Gestão financeira e Dashboard |
