### 1. 📄 Arquivo `README.md`

```markdown
# 🚀 Docker FullStack Ecosystem: Kanban & Finance

Este repositório contém um ecossistema de aplicações integradas (Gerenciamento de Projetos + Gestão Financeira + Hábitos) desenvolvido com **Laravel API** e **Vue.js 3**, rodando inteiramente em **Docker**.

## 🎯 Visão do Projeto

O objetivo é criar uma plataforma unificada onde o esforço (Tarefas) se conecta ao custo (Finanças) e à rotina diária (Hábitos), com suporte a múltiplos usuários, autenticação robusta e interface moderna.

### 🏗️ Módulos do Sistema

#### 1. 🔐 Core & Autenticação (Hub Central)
* **Login & Registro Moderno:** Design "Split-Screen" com validação visual e feedback instantâneo.
* **Hub Unificado:** Dashboard com "Hero Header" que centraliza o acesso aos módulos.
* **Isolamento de Dados:** Arquitetura Multi-tenancy (cada usuário acessa apenas seus dados).
* **Cross-Device Ready:** Sessões e cookies (Sanctum) otimizados para funcionar de forma fluida tanto no PC quanto no navegador do celular na mesma rede local.

#### 2. 📋 Módulo Kanban (Gerenciamento de Tarefas)
* **Quadros Dinâmicos:** Criação/Exclusão de colunas personalizadas.
* **Drag & Drop Inteligente:** Movimentação fluida. Tarefas em colunas personalizadas não são movidas automaticamente sem permissão.
* **Progresso Granular:** Slider de porcentagem (0-100%) e Checklist de Subtarefas.
* **Gestão de Subtarefas:** Adicionar, editar texto, marcar como feito e excluir itens individuais.
* **Automação:** Cards movidos para "Done" completam automaticamente suas subtarefas.

#### 3. 💰 Módulo Financeiro (Gestão de Custos)
* **Dashboard Visual:** Gráficos interativos (Chart.js) de Entradas vs Saídas.
* **Custos por Tarefa:** Integração total com o Kanban, permitindo lançar despesas diretamente dentro de um Card específico.
* **Categorização Inteligente:** Criação de categorias personalizadas (Ex: Alimentação, Lazer) com cores visuais.
* **Parcelamento:** Lançamento automático de despesas parceladas (Ex: 10x de R$ 100).
* **Cálculo em Tempo Real:** O saldo atualiza instantaneamente a cada operação.

#### 4. ☀️ Módulo Diário (Hábitos & To-Do)
* **Foco Diário:** Lista de tarefas rápida com barra de progresso.
* **Hábitos Recorrentes:** Funcionalidade de tarefas que se repetem (ex: Beber Água).
* **Reset Automático:** Botão para iniciar um novo dia, limpando tarefas concluídas.

---

## 🛠️ Stack Tecnológica

| Camada | Tecnologia | Detalhes |
| :--- | :--- | :--- |
| **Backend** | PHP 8.2, Laravel 11 | API RESTful, Sanctum, Eloquent ORM |
| **Testes** | Pest PHP | Testes Automatizados (Feature/Unit) |
| **Frontend** | Vue.js 3 | Composition API, Pinia, Vue Router |
| **Libs Front**| Chart.js, SweetAlert2 | Gráficos e Alertas Modais |
| **UX/UI** | CSS3 Scoped | Flexbox, Grid, Dark Mode Ready |
| **Banco** | MySQL 8 | Relacionamentos e Migrations |
| **DevOps** | Docker | Nginx, PHP-FPM, Node Container |

---

## 🕹️ Guia de Comandos (Ciclo de Vida)

### 🟢 1. Iniciar a Aplicação
Rode este comando para subir os containers em segundo plano.
```bash
docker compose up -d

```

*Acesse o frontend via IP local definido no `.env` (ex: `http://192.168.1.X:5173`).*

### 🧪 2. Rodar Testes Automatizados

O projeto conta com testes de integração usando Pest PHP.

```bash
docker compose exec backend ./vendor/bin/pest

```

### 🔴 3. Finalizar (Desligar Tudo)

```bash
docker compose down

```

### 🛠️ 4. Comandos de Manutenção

**Instalar Dependências (Após um `git pull`):**

```bash
docker compose exec backend composer install
docker compose exec frontend npm install

```

**Rodar Migrations (Atualizar o Banco):**

```bash
docker compose exec backend php artisan migrate

```

---

## 📱 Configuração de Rede Local (PC e Celular)

Para acessar o sistema via celular na mesma rede Wi-Fi e garantir que o Login (Sanctum) funcione sem bloqueios de CORS ou Cookies, configure seus arquivos `.env` com o seu IP da rede local (Ex: `192.168.1.X`):

**No `backend/.env`:**

```ini
APP_URL=[http://192.168.1.](http://192.168.1.)X:8000
FRONTEND_URL=[http://192.168.1.](http://192.168.1.)X:5173
SESSION_DOMAIN=null
SESSION_SECURE_COOKIE=false
SESSION_SAME_SITE=lax
SANCTUM_STATEFUL_DOMAINS=192.168.1.X:5173,localhost:5173,192.168.1.X

```

**No `frontend/.env`:**

```ini
VITE_API_URL=[http://192.168.1.](http://192.168.1.)X:8000/api

```

*Após alterar, rode `docker compose restart frontend` e limpe o cache de rotas do backend (`php artisan route:clear`).*

---

## 📅 Dev Log (Roadmap)

### ✅ Concluído

* [x] Configuração Docker (Nginx, PHP, MySQL, Node)
* [x] **Backend:** CRUD Kanban, Subtarefas, Financeiro, Daily e Auth
* [x] **Frontend:** Integração total com Axios Service (`http.js`) com `withCredentials`
* [x] **UX/UI:** Redesign completo e Gráficos (Chart.js)
* [x] **Features:** Categorias Financeiras, Parcelamento, Subtarefas Editáveis
* [x] **Integração:** Vincular um Custo Financeiro a um Card do Kanban
* [x] **Qualidade:** Testes Automatizados com Pest PHP

### 🚧 Em Desenvolvimento

* [ ] Refatoração e limpeza de código
* [ ] Melhorias de acessibilidade

### 🔮 Futuro

* [ ] Exportação de Relatórios PDF

> 🔗 Para detalhes técnicos dos endpoints, consulte o arquivo [ROTAS.md](https://www.google.com/search?q=./ROTAS.md).