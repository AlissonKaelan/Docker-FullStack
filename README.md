# 📋 Dockerized Kanban Board

Um sistema de gerenciamento de tarefas estilo Kanban, desenvolvido para consolidar conhecimentos em arquitetura **Full Stack baseada em microsserviços e containers**. O projeto foca em performance, isolamento de ambiente e boas práticas de Engenharia de Software.

---

## 🚀 Instalação e Configuração (Quick Start)

Se você acabou de clonar o projeto, siga estes passos para ter o ambiente rodando em minutos.

### 1. Subir a Infraestrutura

Levanta os containers de API (Laravel), Cliente (Vue), Banco de Dados e Servidor Web.

```bash
docker compose up -d

```

### 2. Instalar Dependências

Instala os pacotes do PHP (Composer) e do Node.js (NPM) dentro dos containers.

```bash
docker compose exec backend composer install
docker compose exec frontend npm install

```

### 3. Setup do Banco de Dados

Roda as migrações para criar as tabelas `columns` e `cards`.

```bash
docker compose exec backend php artisan migrate

```

> **Acesso:**
> * 📱 **Aplicação:** [http://localhost:5173](https://www.google.com/search?q=http://localhost:5173)
> * 🔌 **API (Direto):** [http://localhost:8000](https://www.google.com/search?q=http://localhost:8000)
> * 🗄️ **PhpMyAdmin:** [http://localhost:8081](https://www.google.com/search?q=http://localhost:8081)
> 
> 

---

## ⚙️ Workflow de Desenvolvimento

Comandos essenciais para o dia a dia do desenvolvedor.

| Ação | Comando | Descrição |
| --- | --- | --- |
| **Iniciar** | `docker compose up -d` | Sobe todos os serviços e libera o terminal. |
| **Pausar** | `docker compose stop` | Para os containers sem remover (rápido retorno). |
| **Derrubar** | `docker compose down` | Remove containers e redes (limpeza total). |
| **Logs API** | `docker compose logs -f backend` | Monitora erros do Laravel em tempo real. |
| **Logs Front** | `docker compose logs -f frontend` | Monitora compilação do Vite. |

### Acesso aos Terminais (Shell)

Para rodar comandos do artisan ou npm dentro do ambiente:

```bash
# Terminal do Laravel (Backend)
docker compose exec backend bash
# Ex: php artisan make:controller TaskController

# Terminal do Vue (Frontend)
docker compose exec frontend sh
# Ex: npm install axios

```

---

## 🏗️ Arquitetura e Decisões Técnicas

Este projeto foi arquitetado para simular um ambiente de produção escalável. Abaixo, os detalhes de cada camada da aplicação.

### 1. Infraestrutura (Docker)

A arquitetura segue o padrão de microsserviços isolados. Não é necessário ter PHP ou Node instalados na máquina host.

* **App Backend:** PHP 8.2-FPM (Alpine) com extensões GD, BCMath e PDO.
* **App Frontend:** Node.js (Alpine) rodando servidor Vite.
* **Database:** MySQL 8.0 com volumes persistentes.
* **Webserver (Gateway):** Nginx atuando como **Reverse Proxy**, redirecionando o tráfego da porta `8000` para o PHP-FPM.

**Mapeamento de Portas:**
| Serviço | Host | Container | Função |
| :--- | :--- | :--- | :--- |
| **Frontend** | `5173` | `5173` | Hot Reload Vue.js |
| **API Gateway** | `8000` | `80` | Entrada da API REST |
| **PhpMyAdmin** | `8081` | `80` | Gestão visual do MySQL |

### 2. Banco de Dados & Backend

A modelagem de dados foi pensada para suportar a ordenação dinâmica do Kanban.

* **Tabelas:**
* `columns`: Listas do Kanban (To Do, Doing, Done). Possui `order_index`.
* `cards`: As tarefas em si. Relacionamento `1:N` com colunas.


* **Performance:** Utilização de **Eager Loading** (`with('cards')`) na rota `GET /api/kanban` para evitar o problema de N+1 queries.
* **API Endpoints:**
* `PUT /api/cards/{id}`: Endpoint inteligente que detecta se o card apenas mudou de posição ou trocou de coluna.



### 3. Frontend & Interatividade

A interface reativa foi construída para garantir fluidez na UX.

* **Stack:** Vue 3 (Composition API) + Axios + Vite.
* **Drag & Drop:** Implementado com `VueDraggable`.
* **Lógica de Persistência:**
1. O evento `@change` detecta a soltura do card.
2. O Frontend calcula o novo `order_index` baseado nos vizinhos.
3. Envia payload otimizado para a API.
4. *Fallback:* Em caso de erro 500, a interface reverte o movimento visualmente.



---

## 📝 Próximos Passos (Roadmap)

* [ ] Implementar autenticação (Laravel Sanctum).
* [ ] Adicionar WebSockets (Reverb/Pusher) para atualização em tempo real entre usuários.
* [ ] Criar testes automatizados (PestPHP).


1. **Hierarquia Visual:** Uso de Badges, Emojis e tabelas mais limpas. Isso ajuda recrutadores a escanear suas habilidades rapidamente.
2. **Consolidação:** Juntei as "Fases" dentro de uma seção chamada "Arquitetura e Decisões Técnicas". Isso mostra que você não apenas seguiu um tutorial, mas entende **o porquê** de cada peça (Nginx como Proxy, Eager Loading, Lógica do Drag & Drop).
3. **Profissionalismo:** Termos como "Quick Start", "Gateway", "Reverse Proxy" e "Roadmap" dão um tom mais sênior à documentação.
