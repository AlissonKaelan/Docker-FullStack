# 🚀 Docker FullStack Ecosystem: Kanban & Finance

Este repositório contém um ecossistema de aplicações integradas (Gerenciamento de Projetos + Gestão Financeira + Hábitos) desenvolvido com **Laravel API** e **Vue.js 3**, rodando inteiramente em **Docker**.

A arquitetura foi projetada para ser **Agnóstica de Rede (Stateless)**. Isso significa que você pode rodar o projeto no seu PC e acessá-lo imediatamente do seu celular ou tablet na mesma rede Wi-Fi, sem precisar ficar reconfigurando variáveis de IP.

## 🎯 Visão do Projeto

O objetivo é criar uma plataforma unificada onde o esforço (Tarefas) se conecta ao custo (Finanças) e à rotina diária (Hábitos), com suporte a múltiplos usuários, autenticação robusta e interface moderna.

### 🏗️ Módulos do Sistema

#### 1. 🏢 Módulo de Workspaces (Hub Colaborativo)
* **Arquitetura SaaS:** O sistema opera baseado em Workspaces (Projetos).
* **Multiusuário:** Criação de múltiplos projetos e convites de acesso.
* **Controle de Acesso (ACL):** Níveis de permissão granulares (Admin, Editor, Leitor).
* **Isolamento Absoluto:** Tarefas e Finanças pertencem exclusivamente ao Workspace ativo.

#### 2. 🔐 Core & Autenticação (Hub Central)
* **Login & Registro Moderno:** Design "Split-Screen" responsivo.
* **API Stateless:** Autenticação via Bearer Token puro, imune a bloqueios de CSRF cross-device.
* **Cross-Device Ready:** Funciona de forma fluida simultaneamente no PC, Celular ou Tablet sem falhas de Sessão.

#### 3. 📋 Módulo Kanban (Gerenciamento de Tarefas)
* **Quadros Dinâmicos e WebSockets:** Atualização em tempo real das colunas.
* **Drag & Drop Inteligente:** Movimentação fluida entre as etapas.
* **Gestão de Subtarefas:** Adicionar, editar e marcar como feito com barra de progresso.

#### 4. 💰 Módulo Financeiro (Gestão de Custos)
* **Dashboard Visual:** Gráficos interativos (Chart.js) de Entradas vs Saídas.
* **Custos por Tarefa:** Vinculação de despesas diretamente a um Card do Kanban.
* **Parcelamento e Categorização:** Automação de parcelas e gestão visual por cores.

#### 5. ☀️ Módulo Diário (Hábitos & To-Do)
* **Foco Diário & Reset Automático:** Lista rápida para o dia e limpeza automática de concluídos.

---

## 🛠️ Stack Tecnológica

| Camada | Tecnologia | Detalhes |
| :--- | :--- | :--- |
| **Backend** | PHP 8.2, Laravel 11 | API RESTful, Autenticação Token, Eloquent ORM |
| **Testes** | Pest PHP | Testes Automatizados (Feature/Unit) |
| **Frontend** | Vue.js 3 | Composition API, Pinia, Vue Router, Vite |
| **Tempo Real**| Laravel Reverb | WebSockets Nativos |
| **UX/UI** | CSS3 Scoped | Flexbox, Grid, Dark Mode Ready |
| **DevOps** | Docker | Containers isolados (Nginx, PHP-FPM, Node, MySQL) |

---

## 🚀 Guia de Instalação e Execução

### 1. Clonar e Iniciar
```bash
git clone [https://github.com/SeuUsuario/Docker-FullStack.git](https://github.com/SeuUsuario/Docker-FullStack.git)
cd Docker-FullStack
docker compose up -d
```

### 2. Instalar Dependências
```bash
docker compose exec backend composer install
docker compose exec frontend npm install
```

### 3. Configurar o Ambiente (.env)
Copie os arquivos de exemplo:
```bash
cp backend/.env.example backend/.env
cp frontend/.env.example frontend/.env
```

**Configuração Crucial do Backend (`backend/.env`):**
Como o frontend gerencia as requisições dinamicamente, garanta que as variáveis de segurança baseadas em cookies estejam *vazias*:
```ini
SANCTUM_STATEFUL_DOMAINS=
SESSION_DOMAIN=
```

**Configuração do Frontend (`frontend/.env`):**
Não é necessário colocar IP rígido.
```ini
VITE_API_URL=http://localhost:8000/api
VITE_REVERB_HOST=localhost
```

### 4. Preparar o Banco de Dados e Chaves
```bash
docker compose exec backend php artisan key:generate
docker compose exec backend php artisan migrate:fresh --seed
```

### 5. Iniciar os WebSockets (Tempo Real)
```bash
docker compose exec -d backend php artisan reverb:start --host="0.0.0.0" --port="8080"
```

---

## 📱 Como acessar de outro dispositivo (Celular/Tablet)

O frontend foi programado com rotatividade de Host automática (`window.location.hostname`). Para testar no celular:

1. Garanta que o PC e o celular estão na mesma rede Wi-Fi.
2. Descubra o IP do seu PC (ex: `192.168.1.5`). No Windows, use `ipconfig`. No Linux, `ip addr`.
3. Abra o navegador do celular e digite: `http://192.168.1.5:5173`
4. A API e os WebSockets se adaptarão automaticamente para esse endereço!

---

## 🚑 Troubleshooting (Resolução de Problemas Comuns)

Se você tentar acessar do celular e encontrar problemas, verifique o checklist abaixo:

### Erro 1: A página fica carregando e dá `TIMEOUT`
* **Causa:** O Firewall do Windows está bloqueando as portas do Docker.
* **Solução:** Abra o PowerShell como Administrador e rode:
  ```powershell
  New-NetFirewallRule -DisplayName "Docker App Ports" -Direction Inbound -Action Allow -Protocol TCP -LocalPort 5173,8000,8080
  ```

### Erro 2: Estou no WSL2 e o celular ainda dá `TIMEOUT`
* **Causa:** O WSL2 não está repassando a porta da placa Wi-Fi para o Docker.
* **Solução:** Abra o PowerShell como Administrador e crie a ponte manual para as portas:
  ```powershell
  netsh interface portproxy add v4tov4 listenaddress=0.0.0.0 listenport=5173 connectaddress=127.0.0.1 connectport=5173
  netsh interface portproxy add v4tov4 listenaddress=0.0.0.0 listenport=8000 connectaddress=127.0.0.1 connectport=8000
  ```

### Erro 3: Erro 419 (CSRF Token Mismatch) ao fazer Login
* **Causa:** O Laravel está tentando usar Sessões (Cookies) numa requisição externa.
* **Solução:**
  1. No `backend/.env`, garanta que `SANCTUM_STATEFUL_DOMAINS=` está totalmente vazio.
  2. Rode no terminal do PC: `docker compose exec backend php artisan config:clear`.
  3. Acesse por uma Aba Anônima no celular para limpar o cache antigo.