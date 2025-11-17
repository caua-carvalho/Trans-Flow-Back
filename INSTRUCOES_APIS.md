# Instruções de Utilização das APIs

Este documento descreve como utilizar todas as APIs disponíveis no projeto, incluindo endpoints, métodos, parâmetros e exemplos de requisição e resposta.

---

## 1. Containers

### Listar todos os containers
- **Endpoint:** `/api/container`
- **Método:** GET
- **Descrição:** Retorna todos os containers cadastrados.

### Buscar container por ID
- **Endpoint:** `/api/container/{id}`
- **Método:** GET
- **Descrição:** Retorna os dados de um container específico.

### Criar container
- **Endpoint:** `/api/container`
- **Método:** POST
- **Body (JSON):**
  ```json
  {
    "codigo": "string (obrigatório)",
    "status": "string (opcional)",
    "origem": "string (opcional)",
    "destino": "string (opcional)"
  }
  ```

### Atualizar container
- **Endpoint:** `/api/container/{id}`
- **Método:** PUT
- **Body (JSON):**
  ```json
  {
    "codigo": "string (opcional)",
    "status": "string (opcional)",
    "origem": "string (opcional)",
    "destino": "string (opcional)"
  }
  ```

### Atualizar status do container
- **Endpoint:** `/api/container/{id}/status`
- **Método:** PATCH
- **Body (JSON):**
  ```json
  {
    "status": "string (obrigatório)"
  }
  ```

### Deletar container
- **Endpoint:** `/api/container/{id}`
- **Método:** DELETE

---

## 2. Lotes

### Listar todos os lotes
- **Endpoint:** `/api/lote`
- **Método:** GET
- **Query Params:** `container_id` (opcional)

### Buscar lote por ID
- **Endpoint:** `/api/lote/{id}`
- **Método:** GET

### Buscar lote por código
- **Endpoint:** `/api/lote/codigo/{codigo}`
- **Método:** GET

### Criar lote
- **Endpoint:** `/api/lote`
- **Método:** POST
- **Body (JSON):**
  ```json
  {
    "container_id": "int (opcional)",
    "codigo": "string (obrigatório)",
    "status": "string (opcional)",
    "data_envio": "YYYY-MM-DD (obrigatório)",
    "data_recebimento": "YYYY-MM-DD (opcional)",
    "observacoes": "string (opcional)"
  }
  ```

### Atualizar lote
- **Endpoint:** `/api/lote/{id}`
- **Método:** PUT
- **Body (JSON):**
  ```json
  {
    "container_id": "int (opcional)",
    "codigo": "string (opcional)",
    "status": "string (opcional)",
    "observacoes": "string (opcional)"
  }
  ```

### Atualizar status do lote
- **Endpoint:** `/api/lote/{id}/status`
- **Método:** PATCH
- **Body (JSON):**
  ```json
  {
    "status": "string (obrigatório)"
  }
  ```

### Deletar lote
- **Endpoint:** `/api/lote/{id}`
- **Método:** DELETE

---

## 3. Produtos

### Listar todos os produtos
- **Endpoint:** `/api/produto`
- **Método:** GET
- **Query Params:** `lote_id` (opcional)

### Buscar produto por ID
- **Endpoint:** `/api/produto/{id}`
- **Método:** GET

### Criar produto
- **Endpoint:** `/api/produto`
- **Método:** POST
- **Body (JSON):**
  ```json
  {
    "lote_id": "int (opcional)",
    "nome": "string (obrigatório)",
    "status": "string (opcional)",
    "localizacao": "string (opcional)",
    "area": "string (opcional)",
    "prateleira": "string (opcional)",
    "data_expedicao": "YYYY-MM-DD (opcional)"
  }
  ```

### Atualizar produto
- **Endpoint:** `/api/produto/{id}`
- **Método:** PUT
- **Body (JSON):**
  ```json
  {
    "nome": "string (opcional)",
    "status": "string (opcional)",
    "localizacao": "string (opcional)",
    "area": "string (opcional)",
    "prateleira": "string (opcional)",
    "data_expedicao": "YYYY-MM-DD (opcional)"
  }
  ```

### Atualizar status do produto
- **Endpoint:** `/api/produto/{id}/status`
- **Método:** PATCH
- **Body (JSON):**
  ```json
  {
    "status": "string (obrigatório)",
    "localizacao": "string (opcional)"
  }
  ```

### Deletar produto
- **Endpoint:** `/api/produto/{id}`
- **Método:** DELETE

---

## 4. Histórico

### Listar histórico
- **Endpoint:** `/api/historico`
- **Método:** GET
- **Query Params:**
  - `tipo` (opcional)
  - `referencia_id` (opcional)
  - `data_inicio` (opcional, formato YYYY-MM-DD)
  - `data_fim` (opcional, formato YYYY-MM-DD)

### Criar evento no histórico
- **Endpoint:** `/api/historico`
- **Método:** POST
- **Body (JSON):**
  ```json
  {
    "tipo": "string (obrigatório)",
    "referencia_id": "int (obrigatório)",
    "acao": "string (obrigatório)",
    "usuario": "string (opcional)",
    "detalhes": "string (opcional)"
  }
  ```

### Exportar histórico (CSV)
- **Endpoint:** `/api/historico/export`
- **Método:** GET
- **Query Params:**
  - `tipo` (opcional)
  - `data_inicio` (opcional)
  - `data_fim` (opcional)
- **Descrição:** Retorna um arquivo CSV para download.

---

## 5. Dashboard

### Estatísticas gerais
- **Endpoint:** `/api/dashboard/stats`
- **Método:** GET
- **Descrição:** Retorna estatísticas resumidas do sistema (containers, lotes, produtos, eventos do dia).

---

## Observações Gerais
- Todas as respostas seguem o padrão JSON.
- Em caso de erro, será retornado um objeto com mensagem e status.
- Para endpoints que exigem corpo (body), envie o conteúdo em JSON.

---

Dúvidas? Consulte os exemplos de uso ou entre em contato com o time de desenvolvimento.
