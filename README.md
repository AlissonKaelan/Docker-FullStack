# 🚀 Docker FullStack Ecosystem: Kanban & Finance

Este repositório contém um ecossistema de aplicações integradas (Gerenciamento de Projetos + Gestão Financeira + Hábitos) desenvolvido com **Laravel API** e **Vue.js 3**, rodando inteiramente em **Docker**.

## 🎯 Visão do Projeto

O objetivo é criar uma plataforma unificada onde o esforço (Tarefas) se conecta ao custo (Finanças) e à rotina diária (Hábitos), com suporte a múltiplos usuários, autenticação robusta e interface moderna.

### 🏗️ Módulos do Sistema

#### 1. 🔐 Core & Autenticação (Hub Central)

* **Login & Registro Moderno:** Design "Split-Screen" com validação visual e feedback instantâneo.
* **Hub Unificado:** Dashboard com "Hero Header" que centraliza o acesso aos módulos.
* **Isolamento de Dados:** Arquitetura Multi-tenancy (cada usuário acessa apenas seus dados).

#### 2. 📋 Módulo Kanban (Gerenciamento de Tarefas)

* **Quadros Dinâmicos:** Criação/Exclusão de colunas e tarefas ilimitadas.
* **Drag & Drop:** Movimentação fluida de cards entre colunas (To Do, Doing, Done).
* **Progresso Granular:** Slider de porcentagem (0-100%) e Checklist de Subtarefas com barra de progresso visual.
* **Automação:** Cards movidos para "Done" completam automaticamente suas subtarefas.

#### 3. 💰 Módulo Financeiro (Gestão de Custos)

* **Dashboard Visual:** Cards de Saldo, Receita e Despesa com design "Glassmorphism".
* **CRUD Completo:** Adicionar, Editar e Excluir transações com máscaras de moeda (R$) e data automática.
* **Cálculo em Tempo Real:** O saldo atualiza instantaneamente a cada operação.

#### 4. ☀️ Módulo Diário (Hábitos & To-Do)

* **Foco Diário:** Lista de tarefas rápida com barra de progresso.
* **Hábitos Recorrentes:** Funcionalidade de tarefas que se repetem (ex: Beber Água).
* **Reset Automático:** Botão para iniciar um novo dia, limpando tarefas concluídas e resetando os hábitos.

---

## 🛠️ Stack Tecnológica

| Camada | Tecnologia | Detalhes |
| --- | --- | --- |
| **Backend** | PHP 8.2, Laravel 11 | API RESTful, Sanctum, Eloquent ORM |
| **Frontend** | Vue.js 3 | Composition API, Pinia, Vue Router, Axios Custom |
| **UX/UI** | CSS3 Scoped | Flexbox, Grid, Gradientes Modernos, Animações |
| **Banco** | MySQL 8 | Relacionamentos (One-to-Many) e Migrations |
| **DevOps** | Docker | Nginx, PHP-FPM, Node Container (Vite) |

---

## 🕹️ Guia de Comandos (Ciclo de Vida)

Aqui estão os comandos essenciais para operar o projeto no dia a dia.

### 🟢 1. Iniciar a Aplicação

Rode este comando para subir os containers em segundo plano (modo *detach*).

```bash
docker compose up -d

```

*Acesse em: `http://localhost:5173*`

### ⏸️ 2. Pausar a Aplicação

Este comando para os containers mas mantém o estado da memória. Útil para pausas curtas.

```bash
docker compose stop

```

*(Para voltar, basta rodar o comando de iniciar novamente)*

### 🔴 3. Finalizar (Desligar Tudo)

Este comando para e remove os containers e redes criadas. Use ao terminar o dia de trabalho.

```bash
docker compose down

```

### 🛠️ 4. Comandos de Manutenção

Se você baixar atualizações ou mexer no banco de dados, use:

**Instalar Dependências (Se houver novidades no `composer.json` ou `package.json`):**

```bash
docker compose exec backend composer install
docker compose exec frontend npm install

```

**Rodar Migrations (Atualizar o Banco):**

```bash
docker compose exec backend php artisan migrate

```

**Resetar Banco do Zero (Cuidado: Apaga tudo!):**

```bash
docker compose exec backend php artisan migrate:fresh

```

---

## 🔌 Documentação da API

### Rotas Principais (Requer `Bearer Token`)

| Método | Endpoint | Descrição |
| --- | --- | --- |
| **GET** | `/kanban` | Retorna estrutura completa (Colunas -> Cards -> Subtasks) |
| **POST** | `/cards` | Cria nova tarefa vinculada a uma coluna |
| **PUT** | `/cards/{id}` | Atualiza título, descrição, ordem ou porcentagem |
| **POST** | `/subtasks` | Adiciona um item de checklist ao card |
| **GET** | `/balance` | Retorna o objeto financeiro consolidado |
| **GET** | `/daily` | Lista tarefas diárias e hábitos |
| **POST** | `/daily/reset` | Reseta hábitos e limpa tarefas do dia anterior |

---

## 📅 Dev Log (Roadmap)

### ✅ Concluído

* [x] Configuração Docker (Nginx, PHP, MySQL, Node)
* [x] **Backend:** CRUD Kanban, Subtarefas, Financeiro, Daily e Auth
* [x] **Frontend:** Integração total com Axios Service (`http.js`)
* [x] **UX/UI:** Redesign completo (Login Split, Home Hero, Cards Modernos)
* [x] **Features:** Drag & Drop, Checklists, Filtros de Moeda, Hábitos Recorrentes
* [x] **Fix:** Correção da tabela `subtasks` e Persistência de Token

### 🚧 Em Desenvolvimento

* [ ] Refatoração e limpeza de código
* [ ] Testes automatizados

### 🔮 Futuro

* [ ] Gráficos Visuais (Chart.js) no Financeiro
* [ ] Vincular um Custo Financeiro a um Card do Kanban
* [ ] Deploy em servidor Linux (VPS)

---

## 🚀 Instalação Inicial (Primeira vez)

1. Clone o repositório.
2. Copie o arquivo de ambiente:
```bash
cp backend/.env.example backend/.env

```


3. Suba os containers:
```bash
docker compose up -d

```


4. Instale dependências e gere a chave:
```bash
docker compose exec backend composer install
docker compose exec backend php artisan key:generate
docker compose exec frontend npm install

```


5. Crie as tabelas no banco:
```bash
docker compose exec backend php artisan migrate

```