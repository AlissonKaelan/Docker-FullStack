# 📖 Diário de Engenharia (DevLog)

Este documento registra os desafios técnicos encontrados durante a construção da arquitetura Docker Fullstack e as soluções aplicadas.

## 1. Infraestrutura Docker & Nginx

### O Desafio do Volume vs Arquivo
- **Sintoma:** Erro `error mounting ".../nginx/default.conf" ... not a directory`.
- **Causa:** O Docker tentava montar um arquivo local inexistente, criando uma pasta no lugar.
- **Solução:** Remoção do volume incorreto e criação manual do arquivo `nginx/default.conf` com configuração de proxy reverso (`fastcgi_pass`).

### Mapeamento de Pastas (Root)
- **Sintoma:** Tela branca com "File not found" ao acessar o backend.
- **Causa:** Divergência entre o root do Nginx (`/var/www/html/public`) e o volume montado (`/var/www`).
- **Solução:** Ajuste no `default.conf` para apontar corretamente para `/var/www/public`.

## 2. Backend (Laravel API)

### Conexão de Banco de Dados
- **Sintoma:** `php_network_getaddresses: getaddrinfo for kanban-db failed`.
- **Causa:** O .env do Laravel buscava um host incorreto.
- **Solução:** Ajuste de `DB_HOST=db` para alinhar com o nome do serviço no docker-compose.yml.

### Permissões de Escrita
- **Sintoma:** `Failed to open stream: Permission denied`.
- **Causa:** Usuário do processo PHP (www-data) sem permissão de escrita em pastas criadas pelo root.
- **Solução:** Aplicação de permissões (`chmod -R 777 storage`) nas pastas de log e cache.

### API "Silent Fail" (Status 200 com Body Vazio)
- **Sintoma:** Requisições retornavam sucesso (200 OK) mas sem dados JSON.
- **Causa:** O Controller foi criado via artisan mas os métodos estavam vazios, sem a instrução `return`.
- **Solução:** Implementação do retorno explícito: `return response()->json($data);`.

## 3. Frontend (Vue.js & Networking)

### Bloqueio CORS e Rede Docker
- **Sintoma:** Erro `Network Error` ou bloqueio por CORS policy no navegador.
- **Diagnóstico:**
    1. Vite bloqueando conexões externas por padrão.
    2. Laravel rejeitando origens diferentes (porta 5173 vs 8000).
- **Solução:**
    1. Ajuste no `vite.config.js`: `server.host: '0.0.0.0'` e `usePolling: true`.
    2. Criação manual do arquivo `config/cors.php` no Laravel 11 liberando `allowed_origins => ['*']`.

### Erro 500 no Login/Registro (Call to undefined method createToken)
- **Sintoma:** O comando cURL retornava o HTML de uma página de erro do Laravel ou uma mensagem gigante de log no terminal.
- **Causa:** O Model `User.php` padrão do Laravel 11 pode vir sem o Trait `HasApiTokens` importado, impedindo a geração de tokens do Sanctum.
- **Solução:** Adicionada a linha `use Laravel\Sanctum\HasApiTokens;` e incluído o trait `HasApiTokens` dentro da classe `User`.

### Erro de Validação Silencioso
- **Sintoma:** Retorno de "Credenciais inválidas" mesmo após tentar registrar.
- **Causa:** Erros de sintaxe no array de validação (`max":255` e `unique:user`) faziam o registro falhar antes de criar o usuário.
- **Solução:** Correção da sintaxe e do nome da tabela para o plural (`users`).
