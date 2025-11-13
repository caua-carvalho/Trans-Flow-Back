# 📊 Schema do Banco de Dados - TransFlow v2.0

## 🎯 Visão Geral

O banco de dados foi reestruturado para modelar completamente o fluxo de gestão de logística e armazenagem:

```
PORTO (Lotes) 
  ↓
SEPARAÇÃO (Sublotes)
  ↓
CD - ARMAZENAMENTO (Estoque_CD com Localizações)
  ↓
PEDIDOS (Clientes)
  ↓
PREPARAÇÃO E ENVIO (Itens Pedido → Envios)
  ↓
ENTREGA (Confirmação)

↔ LOGÍSTICA REVERSA (Para Defeitos/Devoluções)
```

---

## 📋 Tabelas Implementadas (14 Tabelas)

### 1️⃣ **centros_distribuicao**
Centros de Distribuição que armazenam e preparam produtos.

```sql
id SERIAL PRIMARY KEY
codigo VARCHAR(50) UNIQUE              -- CD-001, CD-002, etc
nome VARCHAR(100)                      -- CD São Paulo
endereco TEXT
capacidade_maxima INTEGER              -- Capacidade total
status VARCHAR(50)                     -- ativo, inativo
data_criacao TIMESTAMP
data_atualizacao TIMESTAMP
```

**Índices:** `cd_status`

---

### 2️⃣ **localizacoes_cd**
Localizações físicas dentro de cada CD (zona, corredor, prateleira, posição).

```sql
id SERIAL PRIMARY KEY
cd_id INTEGER (FK)                     -- Referencia CD
codigo VARCHAR(50)                     -- LOC-A1-001
zona VARCHAR(20)                       -- A, B, C
corredor VARCHAR(20)                   -- 1, 2, 3
prateleira VARCHAR(20)                 -- 1, 2, 3
posicao VARCHAR(20)                    -- 1, 2, 3
capacidade INTEGER                     -- Quantos produtos cabem
status VARCHAR(50)                     -- disponivel, ocupada
data_criacao TIMESTAMP
UNIQUE (cd_id, zona, corredor, prateleira, posicao)
```

**Índices:** `localizacoes_cd`, `localizacoes_status`

---

### 3️⃣ **fornecedores**
Origem dos produtos (fornecedores/portos).

```sql
id SERIAL PRIMARY KEY
codigo VARCHAR(50) UNIQUE              -- FORN-001
nome VARCHAR(100)                      -- Porto de Santos
endereco TEXT
telefone VARCHAR(20)
email VARCHAR(100)
status VARCHAR(50)                     -- ativo, inativo
data_criacao TIMESTAMP
```

**Índices:** Nenhum (tabela pequena, referenciada por fk)

---

### 4️⃣ **clientes**
Clientes que fazem pedidos.

```sql
id SERIAL PRIMARY KEY
codigo VARCHAR(50) UNIQUE              -- CLI-001
nome VARCHAR(100)
endereco TEXT
telefone VARCHAR(20)
email VARCHAR(100)
status VARCHAR(50)                     -- ativo, inativo
data_criacao TIMESTAMP
```

**Índices:** Nenhum (tabela pequena, referenciada por fk)

---

### 5️⃣ **lotes**
Chegada de lotes de produtos no porto.

```sql
id SERIAL PRIMARY KEY
codigo VARCHAR(50) UNIQUE              -- LOTE-2025-001
codigo_barras VARCHAR(100) UNIQUE      -- Código de barras do lote
fornecedor_id INTEGER (FK)
status VARCHAR(50)                     -- em_porto, separado, finalizado
data_chegada_porto TIMESTAMP           -- Quando chegou
data_movimentacao TIMESTAMP            -- Última movimentação
quantidade_total INTEGER               -- Total de produtos
observacoes TEXT
data_criacao TIMESTAMP
data_atualizacao TIMESTAMP
```

**Status Válidos:**
- `em_porto` - Aguardando separação
- `separacao_em_processo` - Sendo separado em sublotes
- `separado` - Já foi separado em sublotes
- `finalizado` - Todos os itens foram processados

**Índices:** `lotes_status`, `lotes_codigo_barras`, `lotes_fornecedor`, `lotes_data_chegada`

---

### 6️⃣ **sublotes**
Separação de um lote em sublotes para envio ao CD.

```sql
id SERIAL PRIMARY KEY
codigo VARCHAR(50) UNIQUE              -- SUBLOTE-2025-001
codigo_barras VARCHAR(100) UNIQUE      -- Código de barras do sublote
lote_id INTEGER (FK)                   -- Lote original (obrigatório)
cd_id INTEGER (FK)                     -- CD de destino
status VARCHAR(50)                     -- em_transito_cd, chegou_cd, armazenado
quantidade_total INTEGER
quantidade_armazenada INTEGER          -- Já armazenado no CD
data_criacao_separacao TIMESTAMP
data_chegada_cd TIMESTAMP
data_movimentacao TIMESTAMP
observacoes TEXT
data_atualizacao TIMESTAMP
```

**Status Válidos:**
- `em_separacao` - Sendo separado
- `em_transito_cd` - Separado, a caminho do CD
- `chegou_cd` - Chegou no CD
- `em_armazenamento` - Sendo armazenado
- `armazenado` - Completamente armazenado

**Índices:** `sublotes_lote`, `sublotes_cd`, `sublotes_status`, `sublotes_codigo_barras`

---

### 7️⃣ **produtos**
Produtos individuais com código de barras único.

```sql
id SERIAL PRIMARY KEY
codigo VARCHAR(50) UNIQUE              -- PROD-2025-00001
codigo_barras VARCHAR(100) UNIQUE      -- Código de barras do produto
nome VARCHAR(100)                      -- Nome do produto
descricao TEXT
sublote_id INTEGER (FK)                -- Qual sublote pertence
status VARCHAR(50)                     -- em_porto, em_transito, armazenado, etc
data_criacao TIMESTAMP
```

**Status Válidos:**
- `em_porto` - No porto
- `em_separacao` - Sendo separado
- `em_transito` - Em trânsito para CD
- `armazenado` - Armazenado no CD
- `em_pedido` - Aguardando pedido
- `preparacao_pendente` - Pedido feito, aguardando separação
- `separado_para_envio` - Pronto para envio
- `em_envio` - Saiu do CD para cliente
- `entregue` - Entregue ao cliente
- `defeito_reversa` - Em processo de logística reversa
- `devolvido` - Devolvido pelo cliente

**Índices:** `produtos_status`, `produtos_codigo_barras`, `produtos_sublote`

---

### 8️⃣ **estoque_cd**
Rastreamento de produtos no CD com localização específica.

```sql
id SERIAL PRIMARY KEY
produto_id INTEGER (FK)                -- Qual produto
localizacao_id INTEGER (FK)            -- Onde está armazenado
cd_id INTEGER (FK)                     -- Em qual CD
status VARCHAR(50)                     -- armazenado, reservado, movimentando
quantidade INTEGER                     -- Sempre 1 para produtos individuais
data_armazenamento TIMESTAMP
data_movimentacao TIMESTAMP
observacoes TEXT
data_atualizacao TIMESTAMP
UNIQUE (produto_id, cd_id)            -- Um produto em apenas um CD
```

**Índices:** `estoque_produto`, `estoque_cd`, `estoque_localizacao`, `estoque_status`

---

### 9️⃣ **pedidos**
Pedidos dos clientes.

```sql
id SERIAL PRIMARY KEY
numero_pedido VARCHAR(50) UNIQUE      -- PED-2025-00001
cliente_id INTEGER (FK)
cd_id INTEGER (FK)                    -- CD que vai preparar
status VARCHAR(50)                    -- pendente_preparacao, preparando, pronto_envio, enviado, entregue
data_pedido TIMESTAMP
data_preparacao_inicio TIMESTAMP
data_preparacao_fim TIMESTAMP
data_saida_cd TIMESTAMP
data_entrega TIMESTAMP
observacoes TEXT
data_atualizacao TIMESTAMP
```

**Status Válidos:**
- `pendente_preparacao` - Novo pedido
- `preparacao_em_processo` - Separando itens
- `pronto_para_envio` - Todos os itens preparados
- `enviado` - Saiu do CD
- `em_entrega` - Em poder do transportista
- `entregue` - Entregue ao cliente
- `cancelado` - Pedido cancelado

**Índices:** `pedidos_numero`, `pedidos_cliente`, `pedidos_status`, `pedidos_cd`, `pedidos_data`

---

### 🔟 **itens_pedido**
Itens individuais de um pedido.

```sql
id SERIAL PRIMARY KEY
pedido_id INTEGER (FK)
produto_id INTEGER (FK)
quantidade INTEGER
status VARCHAR(50)                    -- pendente_separacao, separado, preparado
data_separacao TIMESTAMP
observacoes TEXT
data_criacao TIMESTAMP
```

**Status Válidos:**
- `pendente_separacao` - Aguardando ser separado
- `em_separacao` - Sendo separado
- `separado` - Já foi separado
- `preparado` - Pronto para envio

**Índices:** Nenhum (pequena tabela, consulta por `pedido_id`)

---

### 1️⃣1️⃣ **envios**
Registro de envios dos pedidos preparados.

```sql
id SERIAL PRIMARY KEY
numero_envio VARCHAR(50) UNIQUE       -- ENV-2025-00001
codigo_barras_envio VARCHAR(100) UNIQUE -- Código de barras do pacote
pedido_id INTEGER (FK)
cd_id INTEGER (FK)
cliente_id INTEGER (FK)
status VARCHAR(50)                    -- preparado_para_envio, em_transito, entregue
transportadora VARCHAR(100)           -- Nome da transportadora
numero_rastreamento VARCHAR(100)      -- Código da transportadora para rastreio
data_saida_cd TIMESTAMP
data_entrega TIMESTAMP
data_confirmacao_entrega TIMESTAMP
observacoes TEXT
data_criacao TIMESTAMP
data_atualizacao TIMESTAMP
```

**Status Válidos:**
- `preparado_para_envio` - Pronto, aguardando coleta
- `coletado` - Coletado pela transportadora
- `em_transito` - Em trânsito
- `entregue` - Entregue e confirmado
- `falha_entrega` - Falha na tentativa

**Índices:** `envios_numero`, `envios_pedido`, `envios_status`, `envios_codigo_barras`, `envios_rastreamento`

---

### 1️⃣2️⃣ **logistica_reversa**
Gestão de devoluções e produtos com defeito.

```sql
id SERIAL PRIMARY KEY
numero_devolucao VARCHAR(50) UNIQUE   -- DEV-2025-00001
produto_id INTEGER (FK)
pedido_id INTEGER (FK)
cliente_id INTEGER (FK)
motivo VARCHAR(50)                    -- defeito, dano, troca, outro
descricao_defeito TEXT
status VARCHAR(50)                    -- solicitado, coletando, recebido, analisando, aprovado, recusado
data_solicitacao TIMESTAMP
data_coleta TIMESTAMP
data_recepcao_cd TIMESTAMP
resultado_analise VARCHAR(50)         -- defeito_confirmado, defeito_nao_confirmado
cd_destino_id INTEGER (FK)            -- Para onde volta o produto
observacoes TEXT
data_atualizacao TIMESTAMP
```

**Status Válidos:**
- `solicitado` - Cliente solicitou devolução
- `coletando` - Transportista coletando
- `coletado` - Já coletado
- `em_transito_reversa` - A caminho do CD
- `recebido_cd` - Chegou no CD
- `em_analise` - Analisando defeito
- `defeito_confirmado` - É defeito, será trocado/reembolsado
- `defeito_nao_confirmado` - Sem defeito, será devolvido ao cliente
- `resolvido` - Processo finalizado

**Índices:** `reversa_numero`, `reversa_status`, `reversa_produto`, `reversa_cliente`

---

### 1️⃣3️⃣ **historico**
Log de todas as movimentações e mudanças de status.

```sql
id SERIAL PRIMARY KEY
tipo_entidade VARCHAR(50)             -- lote, sublote, produto, pedido, envio, reversa
id_entidade INTEGER
codigo_entidade VARCHAR(100)          -- Código do item para referência rápida
acao VARCHAR(100)                     -- Criado, Status atualizado, Movimentado, etc
status_anterior VARCHAR(50)
status_novo VARCHAR(50)
localizacao_anterior VARCHAR(100)
localizacao_nova VARCHAR(100)
usuario VARCHAR(100)                  -- Quem fez a ação
detalhes TEXT                         -- JSON com info adicional
data_evento TIMESTAMP
data_criacao TIMESTAMP
```

**Índices:** `historico_tipo`, `historico_id_entidade`, `historico_data`

---

### 1️⃣4️⃣ **movimentacoes**
Registro detalhado de movimentações de produtos.

```sql
id SERIAL PRIMARY KEY
produto_id INTEGER (FK)
tipo_movimentacao VARCHAR(50)         -- separacao, transito, armazenamento, preparacao, envio, devolucao
localizacao_origem VARCHAR(100)
localizacao_destino VARCHAR(100)
cd_origem_id INTEGER (FK)
cd_destino_id INTEGER (FK)
data_movimentacao TIMESTAMP
usuario VARCHAR(100)
observacoes TEXT
```

**Tipos de Movimentação:**
- `separacao` - Sendo separado no porto
- `transito_cd` - Em trânsito para CD
- `armazenamento` - Sendo armazenado
- `preparacao` - Sendo preparado para pedido
- `envio` - Sendo enviado para cliente
- `devolucao` - Devolvido em logística reversa

---

### 1️⃣5️⃣ **usuarios**
Usuários do sistema.

```sql
id SERIAL PRIMARY KEY
nome VARCHAR(100)
email VARCHAR(100) UNIQUE
senha VARCHAR(255)
tipo_usuario VARCHAR(50)              -- admin, gerente, operador
status VARCHAR(50)                    -- ativo, inativo
data_criacao TIMESTAMP
data_atualizacao TIMESTAMP
```

---

## 🔗 Relacionamentos

```
fornecedores (1) ─── (N) lotes
                      │
                      └─ (N) sublotes
                             │
                             ├─ (1) centros_distribuicao
                             │
                             └─ (N) produtos
                                    │
                                    └─ (1) estoque_cd
                                           │
                                           └─ (1) localizacoes_cd

clientes (1) ─── (N) pedidos
                      │
                      ├─ (1) centros_distribuicao
                      │
                      ├─ (N) itens_pedido
                      │      │
                      │      └─ (1) produtos
                      │
                      └─ (N) envios

produtos ─── logistica_reversa
             pedidos
             estoque_cd
```

---

## 📈 Fluxo Completo

### 1. CHEGADA NO PORTO
1. Lote criado (`status = em_porto`)
2. Código de barras gerado para lote
3. Produtos adicionados ao lote
4. Código de barras gerado para cada produto

### 2. SEPARAÇÃO
1. Lote movido para `status = separacao_em_processo`
2. Sublotes criados (agrupar por CD)
3. Cada sublote tem código de barras
4. Produtos associados a sublotes

### 3. TRANSPORTE PARA CD
1. Sublote marcado como `em_transito_cd`
2. Histórico registra saída
3. Movimentações registradas

### 4. ARMAZENAMENTO NO CD
1. Sublote chega: `chegou_cd`
2. Produtos separados em localizações
3. Estoque_CD criado com localização específica
4. Produto marcado como `armazenado`
5. Histórico registra armazenamento

### 5. PEDIDO DO CLIENTE
1. Pedido criado: `pendente_preparacao`
2. Itens do pedido adicionados
3. Produtos reservados

### 6. PREPARAÇÃO
1. Pedido movido para `preparacao_em_processo`
2. Itens separados (retirados do estoque)
3. Itens marcados como `separado`
4. Quando tudo pronto: `pronto_para_envio`

### 7. ENVIO
1. Envio criado com código de barras
2. Número de rastreamento adicionado
3. Saída do CD: `status = em_transito`
4. Histórico registra saída

### 8. ENTREGA
1. Data de entrega confirmada
2. Produto marcado como `entregue`
3. Envio finalizado

### 9. LOGÍSTICA REVERSA (Se houver defeito)
1. Cliente solicita devolução
2. Devolução criada: `status = solicitado`
3. Coleta agendada
4. Recebido no CD: `recebido_cd`
5. Análise: `em_analise`
6. Resultado: `defeito_confirmado` ou `defeito_nao_confirmado`
7. Ação: Reembolso, troca ou devolução

---

## 🎯 Consultas Comuns

### Rastrear um Produto por Código de Barras
```sql
SELECT 
    p.codigo, p.codigo_barras, p.status,
    sl.codigo as sublote,
    ec.status as status_armazenamento,
    lc.zona, lc.corredor, lc.prateleira, lc.posicao,
    h.acao, h.data_evento
FROM produtos p
LEFT JOIN sublotes sl ON p.sublote_id = sl.id
LEFT JOIN estoque_cd ec ON p.id = ec.produto_id
LEFT JOIN localizacoes_cd lc ON ec.localizacao_id = lc.id
LEFT JOIN historico h ON h.id_entidade = p.id AND h.tipo_entidade = 'produto'
WHERE p.codigo_barras = ?
ORDER BY h.data_evento DESC;
```

### Produtos Armazenados em um CD
```sql
SELECT 
    p.codigo, p.nome, p.codigo_barras,
    lc.zona, lc.corredor, lc.prateleira,
    ec.data_armazenamento
FROM estoque_cd ec
JOIN produtos p ON ec.produto_id = p.id
JOIN localizacoes_cd lc ON ec.localizacao_id = lc.id
WHERE ec.cd_id = ? AND ec.status = 'armazenado'
ORDER BY lc.zona, lc.corredor, lc.prateleira;
```

### Pedidos Aguardando Preparação
```sql
SELECT 
    ped.numero_pedido, cli.nome, COUNT(ip.id) as total_itens,
    ped.data_pedido
FROM pedidos ped
JOIN clientes cli ON ped.cliente_id = cli.id
LEFT JOIN itens_pedido ip ON ped.id = ip.pedido_id
WHERE ped.status = 'pendente_preparacao'
GROUP BY ped.id, cli.nome, ped.data_pedido
ORDER BY ped.data_pedido;
```

### Devoluções Pendentes de Análise
```sql
SELECT 
    lr.numero_devolucao, p.nome, cli.nome as cliente,
    lr.motivo, lr.data_solicitacao
FROM logistica_reversa lr
JOIN produtos p ON lr.produto_id = p.id
JOIN clientes cli ON lr.cliente_id = cli.id
WHERE lr.status = 'em_analise'
ORDER BY lr.data_solicitacao;
```

---

## ✅ Implementado

- ✅ 15 tabelas bem estruturadas
- ✅ Relacionamentos apropriados
- ✅ Índices em todas as consultas comuns
- ✅ Suporte a código de barras em todos os níveis
- ✅ Rastreamento completo via histórico
- ✅ Localizações físicas no CD
- ✅ Fluxo completo de logística reversa
- ✅ Auditorias via histórico e movimentações

---

**Criado em:** 13 de Novembro de 2025
**Versão:** 2.0
**Status:** ✅ Pronto para Implementação
