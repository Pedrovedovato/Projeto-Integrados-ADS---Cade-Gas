-- ============================================================
--  CadêGás MVP — Schema do Banco de Dados
--  Versão: 1.1.0  |  Sprint 1
--  Stack: MySQL 5.7+  |  Encoding: UTF-8
--  Alinhado com: Requisitos P0 (US01–US10)
-- ============================================================

CREATE DATABASE IF NOT EXISTS cadgas
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE cadgas;

-- ============================================================
--  TABELA: usuario
--  US01 – Cadastro de consumidor
--  US02 – Login de consumidor
--  US03 – Informar localização (endereço)
-- ============================================================
CREATE TABLE IF NOT EXISTS usuario (
  id_usuario      INT            NOT NULL AUTO_INCREMENT,
  nome            VARCHAR(100)   NOT NULL,
  email           VARCHAR(150)   NOT NULL,
  senha           VARCHAR(255)   NOT NULL COMMENT 'Hash bcrypt — nunca salvar texto puro',
  telefone        VARCHAR(20)        NULL,

  -- Endereço (US03: base para listagem de distribuidores)
  endereco        VARCHAR(200)       NULL,
  cidade          VARCHAR(80)        NULL,
  estado          CHAR(2)            NULL,
  cep             CHAR(9)            NULL COMMENT 'Formato: 00000-000',

  -- Coordenadas opcionais (US11: ordenação por distância — Sprint 2)
  latitude        DECIMAL(10, 7)     NULL,
  longitude       DECIMAL(10, 7)     NULL,

  ativo           TINYINT(1)     NOT NULL DEFAULT 1,
  criado_em       DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em   DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP
                                          ON UPDATE CURRENT_TIMESTAMP,

  CONSTRAINT pk_usuario       PRIMARY KEY (id_usuario),
  CONSTRAINT uq_usuario_email UNIQUE      (email)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COMMENT='Consumidores cadastrados no app — US01, US02, US03';


-- ============================================================
--  TABELA: distribuidor
--  US04 – Visualizar distribuidores disponíveis
--  US13 – Cadastro manual pelo administrador (P1)
--  US15 – Ativar/desativar distribuidor (P1)
-- ============================================================
CREATE TABLE IF NOT EXISTS distribuidor (
  id_distribuidor   INT             NOT NULL AUTO_INCREMENT,
  nome_empresa      VARCHAR(150)    NOT NULL,
  cnpj              CHAR(18)            NULL COMMENT 'Formato: 00.000.000/0000-00',
  responsavel       VARCHAR(100)        NULL,
  email             VARCHAR(150)        NULL,
  telefone          VARCHAR(20)         NULL,

  -- Localização do distribuidor
  endereco          VARCHAR(200)        NULL,
  cidade            VARCHAR(80)         NULL,
  estado            CHAR(2)             NULL,
  cep               CHAR(9)             NULL COMMENT 'Formato: 00000-000',

  -- Coordenadas (US11: ordenação por distância — Sprint 2)
  latitude          DECIMAL(10, 7)      NULL,
  longitude         DECIMAL(10, 7)      NULL,

  -- Taxa de entrega (US04, US07: exibida na listagem e no resumo do pedido)
  taxa_entrega      DECIMAL(10, 2)  NOT NULL DEFAULT 0.00
                                    COMMENT 'Taxa fixa de entrega cobrada por este distribuidor',

  -- US15: campo que controla exibição na listagem (ativo = aparece, inativo = não aparece)
  ativo             TINYINT(1)      NOT NULL DEFAULT 1,

  criado_em         DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                                             ON UPDATE CURRENT_TIMESTAMP,

  CONSTRAINT pk_distribuidor      PRIMARY KEY (id_distribuidor),
  CONSTRAINT uq_distribuidor_cnpj UNIQUE      (cnpj)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COMMENT='Distribuidores cadastrados manualmente pela equipe — US04, US13, US15';


-- ============================================================
--  TABELA: produto
--  US05 – Selecionar produto
--  US14 – Cadastro de produtos e preços (P1)
-- ============================================================
CREATE TABLE IF NOT EXISTS produto (
  id_produto        INT             NOT NULL AUTO_INCREMENT,
  id_distribuidor   INT             NOT NULL,

  -- US05: tipos básicos — Botijão de gás (ex.: P13) ou Galão de água
  nome              VARCHAR(100)    NOT NULL COMMENT 'Ex: Botijão P13, Botijão P45, Galão 20L',
  descricao         TEXT                NULL,
  preco             DECIMAL(10, 2)  NOT NULL,

  -- Controla se o produto aparece para seleção
  disponivel        TINYINT(1)      NOT NULL DEFAULT 1,

  criado_em         DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                                             ON UPDATE CURRENT_TIMESTAMP,

  CONSTRAINT pk_produto               PRIMARY KEY (id_produto),
  CONSTRAINT fk_produto_distribuidor  FOREIGN KEY (id_distribuidor)
    REFERENCES distribuidor(id_distribuidor)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COMMENT='Produtos de gás e água por distribuidor — US05, US14';


-- ============================================================
--  TABELA: pedido
--  US08 – Escolher forma de pagamento
--  US09 – Confirmar pedido
--  US10 – Ver confirmação do pedido
-- ============================================================
CREATE TABLE IF NOT EXISTS pedido (
  id_pedido         INT             NOT NULL AUTO_INCREMENT,
  id_usuario        INT             NOT NULL,
  id_distribuidor   INT             NOT NULL,

  -- US09: status inicial é 'pendente' (equivale a "Pedido realizado" da tela)
  status            ENUM(
                      'pendente',
                      'confirmado',
                      'em_entrega',
                      'entregue',
                      'cancelado'
                    )               NOT NULL DEFAULT 'pendente',

  -- US07: breakdown do valor total (produto + frete)
  subtotal          DECIMAL(10, 2)  NOT NULL DEFAULT 0.00
                                    COMMENT 'Soma dos itens do pedido',
  taxa_entrega      DECIMAL(10, 2)  NOT NULL DEFAULT 0.00
                                    COMMENT 'Snapshot da taxa no momento do pedido',
  total             DECIMAL(10, 2)  NOT NULL DEFAULT 0.00
                                    COMMENT 'subtotal + taxa_entrega',

  -- US08: forma de pagamento offline (nunca pagamento online — fora do MVP)
  forma_pagamento   ENUM(
                      'dinheiro',
                      'pix',
                      'cartao'
                    )               NOT NULL DEFAULT 'dinheiro'
                                    COMMENT 'Pagamento sempre na entrega — sem gateway online',

  -- Endereço de entrega (snapshot do endereço do usuário no momento do pedido)
  endereco_entrega  VARCHAR(200)        NULL,

  observacao        TEXT                NULL,

  criado_em         DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
  atualizado_em     DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                                             ON UPDATE CURRENT_TIMESTAMP,

  CONSTRAINT pk_pedido              PRIMARY KEY (id_pedido),
  CONSTRAINT fk_pedido_usuario      FOREIGN KEY (id_usuario)
    REFERENCES usuario(id_usuario)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT fk_pedido_distribuidor FOREIGN KEY (id_distribuidor)
    REFERENCES distribuidor(id_distribuidor)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COMMENT='Pedidos realizados pelos consumidores — US08, US09, US10';


-- ============================================================
--  TABELA: itens_pedido
--  US06 – Adicionar produto ao carrinho
--  US07 – Visualizar resumo do pedido
-- ============================================================
CREATE TABLE IF NOT EXISTS itens_pedido (
  id_item           INT             NOT NULL AUTO_INCREMENT,
  id_pedido         INT             NOT NULL,
  id_produto        INT             NOT NULL,
  quantidade        INT             NOT NULL DEFAULT 1,

  -- Snapshot do preço: se o distribuidor alterar o preço depois,
  -- o histórico de pedidos não é afetado
  preco_unitario    DECIMAL(10, 2)  NOT NULL
                                    COMMENT 'Preço no momento do pedido (snapshot)',

  CONSTRAINT pk_itens_pedido  PRIMARY KEY (id_item),
  CONSTRAINT fk_item_pedido   FOREIGN KEY (id_pedido)
    REFERENCES pedido(id_pedido)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT fk_item_produto  FOREIGN KEY (id_produto)
    REFERENCES produto(id_produto)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT chk_quantidade   CHECK (quantidade > 0),
  CONSTRAINT chk_preco        CHECK (preco_unitario >= 0)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COMMENT='Itens que compõem cada pedido — US06, US07';


-- ============================================================
--  ÍNDICES (performance em buscas frequentes do backend)
-- ============================================================
CREATE INDEX idx_produto_distribuidor ON produto      (id_distribuidor);
CREATE INDEX idx_pedido_usuario        ON pedido       (id_usuario);
CREATE INDEX idx_pedido_distribuidor   ON pedido       (id_distribuidor);
CREATE INDEX idx_pedido_status         ON pedido       (status);
CREATE INDEX idx_itens_pedido          ON itens_pedido (id_pedido);

-- Índice para listagem de distribuidores ativos (US04 — consulta mais frequente)
CREATE INDEX idx_distribuidor_ativo    ON distribuidor (ativo);

-- Índice para listagem de produtos disponíveis por distribuidor (US05)
CREATE INDEX idx_produto_disponivel    ON produto      (id_distribuidor, disponivel);


-- ============================================================
--  DADOS SEED (distribuidores e produtos para desenvolvimento)
--  Sprint 2 prevê expansão desses seeds
-- ============================================================

INSERT INTO distribuidor
  (nome_empresa, cnpj, responsavel, email, telefone, endereco, cidade, estado, cep, taxa_entrega, latitude, longitude)
VALUES
  ('GásFácil Distribuidora',
   '12.345.678/0001-90', 'João Silva', 'joao@gasfacil.com.br', '(13) 99000-0001',
   'Rua das Palmeiras, 100', 'Bertioga', 'SP', '11250-000', 8.00,
   -23.854, -46.139),

  ('GásRápido Entregas',
   '98.765.432/0001-11', 'Ana Costa', 'ana@gasrapido.com.br', '(13) 99000-0002',
   'Av. Central, 250', 'Bertioga', 'SP', '11250-100', 10.00,
   -23.860, -46.141),

  ('Distribuidora São Jorge',
   '11.222.333/0001-44', 'Carlos Melo', 'carlos@saojorge.com.br', '(13) 99000-0003',
   'Rua das Flores, 40', 'Bertioga', 'SP', '11250-200', 6.00,
   -23.858, -46.135);

INSERT INTO produto (id_distribuidor, nome, descricao, preco)
VALUES
  (1, 'Botijão P13 (13 kg)',  'Botijão residencial padrão',      95.00),
  (1, 'Botijão P45 (45 kg)',  'Botijão industrial / comercial', 310.00),
  (1, 'Galão de água 20L',    'Galão retornável',                25.00),

  (2, 'Botijão P13 (13 kg)',  'Botijão residencial padrão',      92.00),
  (2, 'Galão de água 20L',    'Galão retornável',                23.00),

  (3, 'Botijão P13 (13 kg)',  'Botijão residencial padrão',      90.00),
  (3, 'Botijão P2 (2 kg)',    'Botijão portátil para camping',   35.00);

-- Usuário de seed para desenvolvimento.
-- E-mail: maria@email.com  |  Senha: senha123
-- Hash bcrypt (cost 10) gerado com: php -r "echo password_hash('senha123', PASSWORD_DEFAULT);"
INSERT INTO usuario
  (nome, email, senha, telefone, endereco, cidade, estado, cep)
VALUES
  ('Maria Souza', 'maria@email.com',
   '$2y$10$Wqp.HE2miPGLbuXOFtbTWOB6cLoRaDwRS99p.4Zgp2yKwreHtxrrG',
   '(13) 98888-1234', 'Av. Principal, 50', 'Bertioga', 'SP', '11250-100');

INSERT INTO pedido
  (id_usuario, id_distribuidor, status, subtotal, taxa_entrega, total, forma_pagamento, endereco_entrega)
VALUES
  (1, 1, 'pendente', 95.00, 8.00, 103.00, 'pix', 'Av. Principal, 50 — Bertioga/SP');

INSERT INTO itens_pedido (id_pedido, id_produto, quantidade, preco_unitario)
VALUES (1, 1, 1, 95.00);


-- ============================================================
--  VERIFICAÇÃO
-- ============================================================
SELECT 'Schema CadêGás criado com sucesso!' AS status;
SHOW TABLES;
