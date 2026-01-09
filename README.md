# 🚀 Docker FullStack Ecosystem: Kanban & Finance

Este repositório contém um ecossistema de aplicações integradas (Gerenciamento de Projetos + Gestão Financeira) desenvolvido com **Laravel API** e **Vue.js 3**, rodando inteiramente em **Docker**.

## 🎯 Visão do Projeto

O objetivo é criar uma plataforma unificada onde o esforço (Tarefas) se conecta ao custo (Finanças), com suporte a múltiplos usuários e colaboração em tempo real.

### 🏗️ Módulos do Sistema

#### 1. 🔐 Core & Autenticação (Hub Central)
- **Login & Registro:** Sistema seguro via Laravel Sanctum.
- **Hub Unificado:** Dashboard "Deus View" que resume saldos e tarefas pendentes.
- **Isolamento de Dados:** Cada usuário vê apenas suas próprias informações (Multi-tenancy via ID).

#### 2. 📋 Módulo Kanban (Gerenciamento de Tarefas)
- **Quadros Dinâmicos:** Criação de colunas e tarefas ilimitadas.
- **Drag & Drop:** Interface reativa para mover cards (Powered by `vuedraggable`).
- **Subtarefas:** Checklist interno dentro de cada card com barra de progresso.

#### 3. 💰 Módulo Financeiro (Gestão de Custos)
- **CRUD Completo:** Adicionar, Editar e Excluir transações.
- **Máscaras e Formatação:** Tratamento inteligente de moeda (BRL) e datas.
- **Fluxo de Caixa:** Dashboard com Entradas, Saídas e Saldo em tempo real.

---

## 🛠️ Stack Tecnológica

| Camada | Tecnologia | Detalhes |
| :--- | :--- | :--- |
| **Backend** | PHP 8.2, Laravel 11 | API RESTful, Sanctum, Eloquent ORM |
| **Frontend** | Vue.js 3 | Composition API, Pinia, Vue Router |
| **UX/UI** | CSS3, SweetAlert2 | Flexbox, Grid, Alertas animados |
| **Banco** | MySQL 8 | Relacionamentos e Agregações |
| **DevOps** | Docker | Nginx, PHP-FPM, Node Container |

---

## 🔌 Documentação da API

### Rotas Principais (Requer `Bearer Token`)

| Método | Endpoint | Descrição |
| :--- | :--- | :--- |
| **GET** | `/kanban` | Retorna colunas e cards do usuário logado |
| **POST** | `/cards` | Cria nova tarefa no quadro |
| **GET** | `/transactions` | Lista o histórico financeiro |
| **GET** | `/balance` | Retorna o objeto `{ income, expense, balance }` |
| **DELETE**| `/transactions/{id}` | Remove uma transação e recalcula saldo |

---

## 📅 Dev Log (Roadmap)

### ✅ Concluído
- [x] Configuração Docker (Nginx, PHP, MySQL, Node)
- [x] Backend Kanban (CRUD API + Migrations)
- [x] Frontend Kanban (Vue.js + Drag and Drop)
- [x] Autenticação (Login/Register/Logout com Sanctum)
- [x] Isolamento de Dados (Cada usuário vê apenas o seu)
- [x] **Hub Central:** Tela inicial com resumo dos módulos
- [x] **Módulo Financeiro:** CRUD e Dashboard de Saldo

### 🚧 Em Desenvolvimento
- [ ] **Gráficos Visuais:** Implementação de Chart.js no Financeiro
- [ ] **Integração:** Vincular um custo a um Card do Kanban

### 🔮 Futuro
- [ ] Sistema de Convites e Colaboração em Equipe
- [ ] Deploy em servidor Linux (VPS)

---

## 🚀 Como Rodar o Projeto

1. Clone o repositório.
2. Suba os containers:
```bash
docker compose up -d

```

3. Instale as dependências (Backend e Frontend):

```bash
docker compose exec backend composer install
docker compose exec frontend npm install

```

4. Rode as migrações:

```bash
docker compose exec backend php artisan migrate

```

5. Acesse: `http://localhost:5173`
