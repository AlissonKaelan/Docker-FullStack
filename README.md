# 🚀 Docker FullStack Ecosystem: Kanban & Finance

Este repositório contém um ecossistema de aplicações integradas (Gerenciamento de Projetos + Gestão Financeira) desenvolvido com **Laravel API** e **Vue.js 3**, rodando inteiramente em **Docker**.

## 🎯 Visão do Projeto (Roadmap)

O objetivo é criar uma plataforma unificada onde o esforço (Tarefas) se conecta ao custo (Finanças), com suporte a múltiplos usuários e colaboração em tempo real.

### 🏗️ Módulos do Sistema

#### 1. 🔐 Core & Autenticação (Hub Central)
- **Login & Registro:** Sistema seguro via Laravel Sanctum.
- **Menu Unificado:** Dashboard principal que dá acesso aos módulos.
- **Perfil do Usuário:** Gerenciamento de dados pessoais.

#### 2. 📋 Módulo Kanban (Gerenciamento de Tarefas)
- **Quadros Dinâmicos:** Criação de colunas e tarefas ilimitadas.
- **Drag & Drop:** Interface reativa para mover cards.
- **Status Visual:** Identificação de tarefas por cores e etiquetas.

#### 3. 💰 Módulo Financeiro (Gestão de Custos)
- **Transações:** Registro de Entradas e Saídas.
- **Vínculo com Tarefas:** Associar um custo específico a um Card do Kanban (Ex: "Comprar Domínio" -> R$ 50,00).
- **Dashboard:** Gráficos de fluxo de caixa e custos por projeto.

#### 4. 🤝 Colaboração (Social)
- **Sistema de Convites:** Usuários podem convidar outros por e-mail para participar de um Projeto.
- **Fluxo de Aceite:** O usuário convidado deve aceitar explicitamente para participar.
- **Permissões:** Controle do que o convidado pode fazer (Ex: Apenas visualizar ou Editar).

---

## 🛠️ Stack Tecnológica

- **Backend:** PHP 8.2, Laravel 11
- **Frontend:** Vue.js 3, Composition API, Pinia (State Management), Vue Router
- **Banco de Dados:** MySQL 8
- **Infraestrutura:** Docker & Docker Compose (Nginx, PHP-FPM)
- **Design:** CSS Puro / Flexbox (Futuramente TailwindCSS)

---

## 📅 Próximos Passos (Dev Log)

- [x] Configuração Docker (Nginx, PHP, MySQL, Node)
- [x] Backend Kanban (CRUD API + Migrations)
- [x] Frontend Kanban (Vue.js + Drag and Drop)
- [ ] **Fase 5: Autenticação (Login/Register/Logout)** 🚧 *Em Breve*
- [ ] **Fase 6:** Isolamento de Dados por Usuário (Multi-tenancy simples)
- [ ] **Fase 7:** Criação do Módulo Financeiro
- [ ] **Fase 8:** Integração Tarefa <-> Custo
- [ ] **Fase 9:** Sistema de Convites e Colaboração