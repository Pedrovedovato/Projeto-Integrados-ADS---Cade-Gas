-- Dados de exemplo para o sistema CadêGás

-- Inserir distribuidores
INSERT INTO distribuidor (nome_empresa, telefone, endereco, cidade, estado, cep, taxa_entrega, ativo) VALUES
                                                                                                          ('Distribuidora Rápida', '(11) 98765-4321', 'Rua das Flores, 123', 'São Paulo', 'SP', '01234-567', 10.00, 1),
                                                                                                          ('GasExpress', '(11) 91234-5678', 'Av. Paulista, 1000', 'São Paulo', 'SP', '01310-100', 8.00, 1),
                                                                                                          ('Distribuidora Central', '(11) 99999-8888', 'Rua Central, 456', 'São Paulo', 'SP', '04567-890', 12.00, 1);

-- Inserir produtos
-- Distribuidora Rápida (id_distribuidor = 1) - Gás e Água
INSERT INTO produto (id_distribuidor, nome, descricao, preco, disponivel) VALUES
                                                                              (1, 'Botijão de Gás P13 (13kg)', 'Botijão de gás de cozinha 13kg', 135.00, 1),
                                                                              (1, 'Galão de Água 20L', 'Galão de água mineral 20 litros', 18.00, 1);

-- GasExpress (id_distribuidor = 2) - Apenas Gás
INSERT INTO produto (id_distribuidor, nome, descricao, preco, disponivel) VALUES
                                                                              (2, 'Botijão de Gás P13 (13kg)', 'Botijão de gás de cozinha 13kg', 130.00, 1),
                                                                              (2, 'Botijão de Gás P45 (45kg)', 'Botijão de gás industrial 45kg', 420.00, 1);

-- Distribuidora Central (id_distribuidor = 3) - Apenas Água
INSERT INTO produto (id_distribuidor, nome, descricao, preco, disponivel) VALUES
                                                                              (3, 'Galão de Água 20L', 'Galão de água mineral 20 litros', 16.00, 1),
                                                                              (3, 'Galão de Água 10L', 'Galão de água mineral 10 litros', 9.00, 1);

-- Inserir usuário de teste
-- Email: teste@email.com
-- Senha: 123456
INSERT INTO usuario (nome, email, senha, telefone, endereco, cidade, estado, cep, ativo) VALUES
    ('Usuário Teste', 'teste@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '(11) 99999-9999', 'Rua Teste, 100', 'São Paulo', 'SP', '01234-567', 1);

-- Nota: A senha criptografada acima é "123456"
-- Para criar outras senhas use: echo password_hash('sua_senha', PASSWORD_BCRYPT);
