-- ============================================================
-- TransFlow - Sistema de Gestão de Logística e Armazenagem
-- ============================================================
-- Fluxo: Porto -> CD (Armazenamento) -> Pedido -> Envio
-- Com suporte a Logística Reversa

-- Tabela de Centros de Distribuição
CREATE TABLE IF NOT EXISTS centros_distribuicao (
    id SERIAL PRIMARY KEY,
    codigo VARCHAR(50) UNIQUE NOT NULL,
    nome VARCHAR(100) NOT NULL,
    endereco TEXT,
    capacidade_maxima INTEGER,
    status VARCHAR(50) DEFAULT 'ativo',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP
);

-- Tabela de Localizações de Armazenamento no CD
CREATE TABLE IF NOT EXISTS localizacoes_cd (
    id SERIAL PRIMARY KEY,
    cd_id INTEGER NOT NULL REFERENCES centros_distribuicao(id) ON DELETE CASCADE,
    codigo VARCHAR(50) NOT NULL,
    zona VARCHAR(20),
    corredor VARCHAR(20),
    prateleira VARCHAR(20),
    posicao VARCHAR(20),
    capacidade INTEGER,
    status VARCHAR(50) DEFAULT 'disponivel',
    UNIQUE(cd_id, zona, corredor, prateleira, posicao),
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Fornecedores/Origens
CREATE TABLE IF NOT EXISTS fornecedores (
    id SERIAL PRIMARY KEY,
    codigo VARCHAR(50) UNIQUE NOT NULL,
    nome VARCHAR(100) NOT NULL,
    endereco TEXT,
    telefone VARCHAR(20),
    email VARCHAR(100),
    status VARCHAR(50) DEFAULT 'ativo',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Clientes (Destinos)
CREATE TABLE IF NOT EXISTS clientes (
    id SERIAL PRIMARY KEY,
    codigo VARCHAR(50) UNIQUE NOT NULL,
    nome VARCHAR(100) NOT NULL,
    endereco TEXT,
    telefone VARCHAR(20),
    email VARCHAR(100),
    status VARCHAR(50) DEFAULT 'ativo',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Lotes (Chegada no Porto)
CREATE TABLE IF NOT EXISTS lotes (
    id SERIAL PRIMARY KEY,
    codigo VARCHAR(50) UNIQUE NOT NULL,
    codigo_barras VARCHAR(100) UNIQUE,
    fornecedor_id INTEGER REFERENCES fornecedores(id),
    status VARCHAR(50) NOT NULL DEFAULT 'em_porto',
    data_chegada_porto TIMESTAMP NOT NULL,
    data_movimentacao TIMESTAMP,
    quantidade_total INTEGER,
    observacoes TEXT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP
);

-- Tabela de Sublotes (Separação do Lote Original para CD)
CREATE TABLE IF NOT EXISTS sublotes (
    id SERIAL PRIMARY KEY,
    codigo VARCHAR(50) UNIQUE NOT NULL,
    codigo_barras VARCHAR(100) UNIQUE,
    lote_id INTEGER NOT NULL REFERENCES lotes(id) ON DELETE CASCADE,
    cd_id INTEGER REFERENCES centros_distribuicao(id),
    status VARCHAR(50) NOT NULL DEFAULT 'em_transito_cd',
    quantidade_total INTEGER,
    quantidade_armazenada INTEGER DEFAULT 0,
    data_criacao_separacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_chegada_cd TIMESTAMP,
    data_movimentacao TIMESTAMP,
    observacoes TEXT,
    data_atualizacao TIMESTAMP
);

-- Tabela de Produtos (Itens Individuais)
CREATE TABLE IF NOT EXISTS produtos (
    id SERIAL PRIMARY KEY,
    codigo VARCHAR(50) UNIQUE NOT NULL,
    codigo_barras VARCHAR(100) UNIQUE NOT NULL,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    sublote_id INTEGER REFERENCES sublotes(id) ON DELETE SET NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'em_porto',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Estoque no CD (Rastreamento de Localização)
CREATE TABLE IF NOT EXISTS estoque_cd (
    id SERIAL PRIMARY KEY,
    produto_id INTEGER NOT NULL REFERENCES produtos(id) ON DELETE CASCADE,
    localizacao_id INTEGER REFERENCES localizacoes_cd(id) ON DELETE SET NULL,
    cd_id INTEGER NOT NULL REFERENCES centros_distribuicao(id) ON DELETE CASCADE,
    status VARCHAR(50) NOT NULL DEFAULT 'armazenado',
    quantidade INTEGER DEFAULT 1,
    data_armazenamento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_movimentacao TIMESTAMP,
    observacoes TEXT,
    UNIQUE(produto_id, cd_id),
    data_atualizacao TIMESTAMP
);

-- Tabela de Pedidos dos Clientes
CREATE TABLE IF NOT EXISTS pedidos (
    id SERIAL PRIMARY KEY,
    numero_pedido VARCHAR(50) UNIQUE NOT NULL,
    cliente_id INTEGER NOT NULL REFERENCES clientes(id),
    cd_id INTEGER NOT NULL REFERENCES centros_distribuicao(id),
    status VARCHAR(50) NOT NULL DEFAULT 'pendente_preparacao',
    data_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_preparacao_inicio TIMESTAMP,
    data_preparacao_fim TIMESTAMP,
    data_saida_cd TIMESTAMP,
    data_entrega TIMESTAMP,
    observacoes TEXT,
    data_atualizacao TIMESTAMP
);

-- Tabela de Itens do Pedido
CREATE TABLE IF NOT EXISTS itens_pedido (
    id SERIAL PRIMARY KEY,
    pedido_id INTEGER NOT NULL REFERENCES pedidos(id) ON DELETE CASCADE,
    produto_id INTEGER NOT NULL REFERENCES produtos(id),
    quantidade INTEGER NOT NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'pendente_separacao',
    data_separacao TIMESTAMP,
    observacoes TEXT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Envios
CREATE TABLE IF NOT EXISTS envios (
    id SERIAL PRIMARY KEY,
    numero_envio VARCHAR(50) UNIQUE NOT NULL,
    codigo_barras_envio VARCHAR(100) UNIQUE,
    pedido_id INTEGER NOT NULL REFERENCES pedidos(id) ON DELETE CASCADE,
    cd_id INTEGER NOT NULL REFERENCES centros_distribuicao(id),
    cliente_id INTEGER NOT NULL REFERENCES clientes(id),
    status VARCHAR(50) NOT NULL DEFAULT 'preparado_para_envio',
    transportadora VARCHAR(100),
    numero_rastreamento VARCHAR(100),
    data_saida_cd TIMESTAMP NOT NULL,
    data_entrega TIMESTAMP,
    data_confirmacao_entrega TIMESTAMP,
    observacoes TEXT,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP
);

-- Tabela de Logística Reversa (Devoluções e Defeitos)
CREATE TABLE IF NOT EXISTS logistica_reversa (
    id SERIAL PRIMARY KEY,
    numero_devolucao VARCHAR(50) UNIQUE NOT NULL,
    produto_id INTEGER REFERENCES produtos(id),
    pedido_id INTEGER REFERENCES pedidos(id),
    cliente_id INTEGER REFERENCES clientes(id),
    motivo VARCHAR(50) NOT NULL,
    descricao_defeito TEXT,
    status VARCHAR(50) NOT NULL DEFAULT 'solicitado',
    data_solicitacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_coleta TIMESTAMP,
    data_recepcao_cd TIMESTAMP,
    resultado_analise VARCHAR(50),
    cd_destino_id INTEGER REFERENCES centros_distribuicao(id),
    observacoes TEXT,
    data_atualizacao TIMESTAMP
);

-- Tabela de Histórico de Movimentações
CREATE TABLE IF NOT EXISTS historico (
    id SERIAL PRIMARY KEY,
    tipo_entidade VARCHAR(50) NOT NULL,
    id_entidade INTEGER NOT NULL,
    codigo_entidade VARCHAR(100),
    acao VARCHAR(100) NOT NULL,
    status_anterior VARCHAR(50),
    status_novo VARCHAR(50),
    localizacao_anterior VARCHAR(100),
    localizacao_nova VARCHAR(100),
    usuario VARCHAR(100),
    detalhes TEXT,
    data_evento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Movimentação de Produtos
CREATE TABLE IF NOT EXISTS movimentacoes (
    id SERIAL PRIMARY KEY,
    produto_id INTEGER NOT NULL REFERENCES produtos(id) ON DELETE CASCADE,
    tipo_movimentacao VARCHAR(50) NOT NULL,
    localizacao_origem VARCHAR(100),
    localizacao_destino VARCHAR(100),
    cd_origem_id INTEGER REFERENCES centros_distribuicao(id),
    cd_destino_id INTEGER REFERENCES centros_distribuicao(id),
    data_movimentacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usuario VARCHAR(100),
    observacoes TEXT
);

-- Tabela de Usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id SERIAL PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    tipo_usuario VARCHAR(50) DEFAULT 'operador',
    status VARCHAR(50) DEFAULT 'ativo',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP
);

-- ============================================================
-- ÍNDICES PARA PERFORMANCE
-- ============================================================

-- Índices de Lotes
CREATE INDEX IF NOT EXISTS idx_lotes_status ON lotes(status);
CREATE INDEX IF NOT EXISTS idx_lotes_codigo_barras ON lotes(codigo_barras);
CREATE INDEX IF NOT EXISTS idx_lotes_fornecedor ON lotes(fornecedor_id);
CREATE INDEX IF NOT EXISTS idx_lotes_data_chegada ON lotes(data_chegada_porto);

-- Índices de Sublotes
CREATE INDEX IF NOT EXISTS idx_sublotes_lote ON sublotes(lote_id);
CREATE INDEX IF NOT EXISTS idx_sublotes_cd ON sublotes(cd_id);
CREATE INDEX IF NOT EXISTS idx_sublotes_status ON sublotes(status);
CREATE INDEX IF NOT EXISTS idx_sublotes_codigo_barras ON sublotes(codigo_barras);

-- Índices de Produtos
CREATE INDEX IF NOT EXISTS idx_produtos_status ON produtos(status);
CREATE INDEX IF NOT EXISTS idx_produtos_codigo_barras ON produtos(codigo_barras);
CREATE INDEX IF NOT EXISTS idx_produtos_sublote ON produtos(sublote_id);

-- Índices de Estoque
CREATE INDEX IF NOT EXISTS idx_estoque_produto ON estoque_cd(produto_id);
CREATE INDEX IF NOT EXISTS idx_estoque_cd ON estoque_cd(cd_id);
CREATE INDEX IF NOT EXISTS idx_estoque_localizacao ON estoque_cd(localizacao_id);
CREATE INDEX IF NOT EXISTS idx_estoque_status ON estoque_cd(status);

-- Índices de Pedidos
CREATE INDEX IF NOT EXISTS idx_pedidos_numero ON pedidos(numero_pedido);
CREATE INDEX IF NOT EXISTS idx_pedidos_cliente ON pedidos(cliente_id);
CREATE INDEX IF NOT EXISTS idx_pedidos_status ON pedidos(status);
CREATE INDEX IF NOT EXISTS idx_pedidos_cd ON pedidos(cd_id);
CREATE INDEX IF NOT EXISTS idx_pedidos_data ON pedidos(data_pedido);

-- Índices de Envios
CREATE INDEX IF NOT EXISTS idx_envios_numero ON envios(numero_envio);
CREATE INDEX IF NOT EXISTS idx_envios_pedido ON envios(pedido_id);
CREATE INDEX IF NOT EXISTS idx_envios_status ON envios(status);
CREATE INDEX IF NOT EXISTS idx_envios_codigo_barras ON envios(codigo_barras_envio);
CREATE INDEX IF NOT EXISTS idx_envios_rastreamento ON envios(numero_rastreamento);

-- Índices de Logística Reversa
CREATE INDEX IF NOT EXISTS idx_reversa_numero ON logistica_reversa(numero_devolucao);
CREATE INDEX IF NOT EXISTS idx_reversa_status ON logistica_reversa(status);
CREATE INDEX IF NOT EXISTS idx_reversa_produto ON logistica_reversa(produto_id);
CREATE INDEX IF NOT EXISTS idx_reversa_cliente ON logistica_reversa(cliente_id);

-- Índices de Histórico
CREATE INDEX IF NOT EXISTS idx_historico_tipo ON historico(tipo_entidade);
CREATE INDEX IF NOT EXISTS idx_historico_id_entidade ON historico(id_entidade);
CREATE INDEX IF NOT EXISTS idx_historico_data ON historico(data_evento);

-- Índices de Localizações
CREATE INDEX IF NOT EXISTS idx_localizacoes_cd ON localizacoes_cd(cd_id);
CREATE INDEX IF NOT EXISTS idx_localizacoes_status ON localizacoes_cd(status);

-- Índices de Centros de Distribuição
CREATE INDEX IF NOT EXISTS idx_cd_status ON centros_distribuicao(status);

-- ============================================================
-- CONSTRAINTS ADICIONAIS
-- ============================================================

-- Garantir que um produto tem apenas uma localização no mesmo CD
ALTER TABLE estoque_cd ADD CONSTRAINT uq_produto_cd UNIQUE (produto_id, cd_id);
