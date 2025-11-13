# Backend TransFlow API

Este é o backend da aplicação TransFlow, desenvolvido em PHP com PostgreSQL.

## Estrutura do Projeto

```
Trans-Flow-Back/
├── api/
│   ├── index.php           # Ponto de entrada da API
│   └── .htaccess           # Configuração de rewrite
├── config/
│   └── database.php        # Configuração de conexão com BD
├── controllers/
│   ├── ContainerController.php
│   ├── LoteController.php
│   ├── ProdutoController.php
│   ├── HistoricoController.php
│   └── DashboardController.php
├── utils/
│   ├── Response.php        # Classe para padronizar respostas
│   └── Router.php          # Classe de roteamento simples
├── sql/
│   └── create_tables.sql   # Script de criação de tabelas
└── README.md
```

## Configuração do Banco de Dados

### 1. Criar banco de dados PostgreSQL

```bash
psql -U root
CREATE DATABASE transflow;
```

### 2. Executar script SQL

```bash
psql -U root -d transflow -f sql/create_tables.sql
```

## Configuração de Ambiente

As configurações de conexão estão no arquivo `config/database.php`:

- **Host:** localhost
- **Porta:** 5432
- **Banco:** transflow
- **Usuário:** root
- **Senha:** (vazia)

## Executando a API

### Via XAMPP

1. Copie a pasta `Trans-Flow-Back` para `c:\xampp\htdocs\`
2. Acesse: `http://localhost/Trans-Flow-Back/api/`

### Testando a API

```bash
# Listar todos os containers
curl http://localhost/Trans-Flow-Back/api/containers

# Obter estatísticas
curl http://localhost/Trans-Flow-Back/api/dashboard/stats
```

## Executando com Docker (recomendado para servidor)

Pré-requisitos: Docker e Docker Compose instalados no servidor.

1. Construa e suba os serviços (na raiz do projeto):

```powershell
docker-compose up -d --build
```

2. Serviços disponíveis após subir:
- App (PHP + Apache): http://localhost:8080  (mapeado para `/api`)
- Adminer (UI DB): http://localhost:8081
- Postgres: exposto na porta 5432

3. A inicialização do Postgres executará automaticamente `sql/create_tables.sql` (o arquivo está montado em `/docker-entrypoint-initdb.d`).

4. Para parar e remover containers:

```powershell
docker-compose down -v
```

5. Variáveis de ambiente de conexão (usadas pelo container app):

- `DB_HOST` (padrão: `db`)
- `DB_PORT` (padrão: `5432`)
- `DB_NAME` (padrão: `transflow`)
- `DB_USER` (padrão: `root`)
- `DB_PASS` (padrão: `root`)

Se necessário, ajuste `docker-compose.yml` para alterar credenciais/portas.


## Endpoints Disponíveis

### Containers
- `GET /containers` - Listar todos
- `GET /containers/{id}` - Obter por ID
- `POST /containers` - Criar novo
- `PUT /containers/{id}` - Atualizar
- `PATCH /containers/{id}/status` - Atualizar status
- `DELETE /containers/{id}` - Deletar

### Lotes
- `GET /lotes` - Listar todos
- `GET /lotes/{id}` - Obter por ID
- `GET /lotes/codigo/{codigo}` - Obter por código
- `POST /lotes` - Criar novo
- `PUT /lotes/{id}` - Atualizar
- `PATCH /lotes/{id}/status` - Atualizar status
- `DELETE /lotes/{id}` - Deletar

### Produtos
- `GET /produtos` - Listar todos
- `GET /produtos/{id}` - Obter por ID
- `POST /produtos` - Criar novo
- `PUT /produtos/{id}` - Atualizar
- `PATCH /produtos/{id}/status` - Atualizar status e localização
- `DELETE /produtos/{id}` - Deletar

### Histórico
- `GET /historico` - Listar com filtros (tipo, referencia_id, data_inicio, data_fim)
- `POST /historico` - Registrar novo evento
- `GET /historico/export` - Exportar CSV

### Dashboard
- `GET /dashboard/stats` - Obter estatísticas gerais

## Formato de Resposta

Sucesso (200):
```json
{
  "success": true,
  "data": [...],
  "message": "Operação realizada com sucesso"
}
```

Criado (201):
```json
{
  "success": true,
  "data": {"id": 1},
  "message": "Recurso criado com sucesso"
}
```

Erro (400, 404, 500):
```json
{
  "success": false,
  "error": "Mensagem de erro"
}
```

## CORS

O backend está configurado para aceitar requisições CORS de qualquer origem.

## Desenvolvimento

Para adicionar novos endpoints:

1. Crie um novo Controller em `controllers/`
2. Implemente os métodos necessários
3. Adicione as rotas em `api/index.php`

## Documentação Completa

Veja `README_API.md` e `README_ENDPOINTS.md` para documentação detalhada dos endpoints.