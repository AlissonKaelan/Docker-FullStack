# Docker FullStack Ecosystem: Kanban & Finance

Este repositório contém um ecossistema de aplicações integradas (Gerenciamento de Projetos + Gestão Financeira + Hábitos) desenvolvido com Laravel API e Vue.js 3, rodando inteiramente em Docker.

A arquitetura foi projetada para ser Agnóstica de Rede (Stateless). Isso significa que você pode rodar o projeto no seu PC e acessá-lo imediatamente do seu celular ou tablet na mesma rede Wi-Fi, sem precisar ficar reconfigurando variáveis de IP.

## Pré-requisitos

Para rodar este projeto, você precisará ter instalado em sua máquina:
* Docker Desktop
* Ubuntu (ou WSL2 configurado com Ubuntu caso esteja no Windows)

## Visão do Projeto

O objetivo é criar uma plataforma unificada onde o esforço (Tarefas) se conecta ao custo (Finanças) e à rotina diária (Hábitos), com suporte a múltiplos usuários, autenticação robusta e interface moderna.

### Módulos do Sistema

#### 1. Módulo de Workspaces (Hub Colaborativo)
* Arquitetura SaaS: O sistema opera baseado em Workspaces (Projetos).
* Multiusuário: Criação de múltiplos projetos e convites de acesso.
* Controle de Acesso (ACL): Níveis de permissão granulares (Admin, Editor, Leitor).
* Isolamento Absoluto: Tarefas e Finanças pertencem exclusivamente ao Workspace ativo.

#### 2. Core & Autenticação (Hub Central)
* Login & Registro Moderno: Design "Split-Screen" responsivo.
* API Stateless: Autenticação via Bearer Token puro, imune a bloqueios de CSRF cross-device.
* Cross-Device Ready: Funciona de forma fluida simultaneamente no PC, Celular ou Tablet sem falhas de Sessão.

#### 3. Módulo Kanban (Gerenciamento de Tarefas)
* Quadros Dinâmicos e WebSockets: Atualização em tempo real das colunas.
* Drag & Drop Inteligente: Movimentação fluida entre as etapas.
* Gestão de Subtarefas: Adicionar, editar e marcar como feito com barra de progresso.

#### 4. Módulo Financeiro (Gestão de Custos)
* Dashboard Visual: Gráficos interativos (Chart.js) de Entradas vs Saídas.
* Custos por Tarefa: Vinculação de despesas diretamente a um Card do Kanban.
* Parcelamento e Categorização: Automação de parcelas e gestão visual por cores.

#### 5. Módulo Diário (Hábitos & To-Do)
* Foco Diário & Reset Automático: Lista rápida para o dia e limpeza automática de concluídos.

---

## Stack Tecnológica

| Camada | Tecnologia | Detalhes |
| :--- | :--- | :--- |
| Backend | PHP 8.2, Laravel 11 | API RESTful, Autenticação Token, Eloquent ORM |
| Testes | Pest PHP | Testes Automatizados (Feature/Unit) |
| Frontend | Vue.js 3 | Composition API, Pinia, Vue Router, Vite |
| Tempo Real | Laravel Reverb | WebSockets Nativos |
| UX/UI | CSS3 Scoped | Flexbox, Grid, Dark Mode Ready |
| DevOps | Docker | Containers isolados (Nginx, PHP-FPM, Node, MySQL) |

---

## Guia de Instalação e Execução

### 1. Clonar e Iniciar
```bash
git clone [https://github.com/SeuUsuario/Docker-FullStack.git](https://github.com/SeuUsuario/Docker-FullStack.git)
cd Docker-FullStack
docker compose up -d

```

Nota: Se o container do frontend ficar reiniciando em loop, veja a seção de Troubleshooting abaixo para instalar as dependências.

### 2. Instalar Dependências

```bash
docker compose exec backend composer install
docker compose run --rm frontend npm install

```

Usamos `run --rm` no frontend na primeira vez para garantir a criação correta da pasta node_modules antes de tentar subir o container. Após isso, garanta que o frontend está rodando: `docker compose up -d frontend`.

### 3. Configurar o Ambiente (.env)

Copie os arquivos de exemplo para criar seus arquivos de ambiente:

```bash
cp backend/.env.example backend/.env
cp frontend/.env.example frontend/.env
cp .env.example .env

```

Configuração Crucial do Backend (backend/.env):
Como o projeto roda em containers, a conexão com o banco de dados deve ser alterada de SQLite (padrão do Laravel) para as credenciais do MariaDB configuradas no Docker. Edite o arquivo e deixe exatamente assim:

```ini
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=kanban_db
DB_USERNAME=root
DB_PASSWORD=secret

```

(Caso queira customizar o nome do banco ou senha, você deve alterar essas mesmas informações no arquivo `.env` da raiz do projeto e reconstruir os containers).

Ainda no `backend/.env`, garanta que as variáveis de segurança baseadas em cookies estejam vazias:

```ini
SANCTUM_STATEFUL_DOMAINS=
SESSION_DOMAIN=

```

Configuração do Frontend (frontend/.env):
Não é necessário colocar IP rígido.

```ini
VITE_API_URL=http://localhost:8000/api
VITE_REVERB_HOST=localhost

```

### 4. Preparar o Banco de Dados e Chaves

```bash
docker compose exec backend php artisan key:generate
docker compose exec backend php artisan config:clear
docker compose exec backend php artisan migrate:fresh --seed

```

### 5. Iniciar os WebSockets (Tempo Real)

```bash
docker compose exec -d backend php artisan reverb:start --host="0.0.0.0" --port="8080"

```

---

## Como acessar de outro dispositivo (Celular/Tablet)

O frontend foi programado com rotatividade de Host automática (window.location.hostname). Para testar no celular:

1. Garanta que o PC e o celular estão na mesma rede Wi-Fi.
2. Descubra o IP do seu PC (ex: 192.168.1.5). No Windows, use ipconfig. No Linux, ip addr.
3. Abra o navegador do celular e digite: http://192.168.1.5:5173
4. A API e os WebSockets se adaptarão automaticamente para esse endereço!

---

## Troubleshooting (Resolução de Problemas Comuns)

### Erro: Container frontend reiniciando em loop (sh: vite: not found)

* Causa: A pasta node_modules (que contém o Vite) não é versionada no Git. Ao clonar, o container tenta iniciar o Vite e falha.
* Solução:
1. Pare o container: `docker compose stop frontend`
2. Instale via container temporário: `docker compose run --rm frontend npm install`
3. Religue o container: `docker compose up -d frontend`



### Erro: Database file at path does not exist (SQLite)

* Causa: O Laravel está tentando usar SQLite, mas o projeto usa MySQL/MariaDB.
* Solução: Edite o arquivo `backend/.env`, mude `DB_CONNECTION=sqlite` para `DB_CONNECTION=mysql`, e defina `DB_HOST=db` (veja o passo 3 da instalação). Depois, rode `docker compose exec backend php artisan config:clear`.

### Erro: No application encryption key has been specified

* Causa: O arquivo `.env` foi criado, mas a chave de criptografia está vazia.
* Solução: Rode o comando `docker compose exec backend php artisan key:generate` seguido de `php artisan config:clear`.

### Erro: A página fica carregando e dá TIMEOUT no celular

* Causa: O Firewall do Windows ou o WSL2 estão bloqueando a conexão.
* Solução para Firewall:
Abra o PowerShell como Administrador e rode:
`New-NetFirewallRule -DisplayName "Docker App Ports" -Direction Inbound -Action Allow -Protocol TCP -LocalPort 5173,8000,8080`
* Solução para o WSL2 (Forçar ponte de portas):
Abra o PowerShell como Administrador e rode:
`netsh interface portproxy add v4tov4 listenaddress=0.0.0.0 listenport=5173 connectaddress=127.0.0.1 connectport=5173`
(Faça o mesmo para as portas 8000 e 8080).
