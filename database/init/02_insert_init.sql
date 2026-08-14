SET NAMES utf8mb4;

INSERT INTO categorias (nome, descricao) VALUES
('Eletrônicos', 'Dispositivos eletrônicos e acessórios'),
('Informática', 'Computadores, peças e periféricos'),
('Escritório', 'Móveis e materiais de escritório');

INSERT INTO clientes (nome, email, telefone) VALUES
('João Silva', 'joao.silva@email.com', '(11) 99999-1111'),
('Maria Oliveira', 'maria.oliveira@email.com', '(11) 98888-2222'),
('Carlos Santos', 'carlos.santos@email.com', '(11) 97777-3333');

INSERT INTO produtos (categoria_id, nome, descricao, preco, estoque) VALUES
(1, 'Smartphone XYZ', 'Smartphone tela 6.5, 128GB', 1500.00, 50),
(2, 'Notebook Pro', 'Notebook Intel Core i7, 16GB RAM', 4500.00, 20),
(2, 'Mouse Sem Fio', 'Mouse ergonômico bluetooth', 120.00, 100),
(3, 'Cadeira Ergonômica', 'Cadeira de escritório com ajuste de altura', 850.00, 15),
(2, 'Teclado Mecânico', 'Teclado mecânico switch brown com LED RGB', 350.00, 40),
(1, 'Fone de Ouvido Bluetooth', 'Fone over-ear com cancelamento ativo de ruído', 299.90, 60);

INSERT INTO vendas (cliente_id, total) VALUES
(1, 1620.00),
(2, 4500.00),
(3, 649.90); -- Nova venda para o cliente 3

INSERT INTO itens_venda (venda_id, produto_id, quantidade, preco_unitario) VALUES
(1, 1, 1, 1500.00),
(1, 3, 1, 120.00);

INSERT INTO itens_venda (venda_id, produto_id, quantidade, preco_unitario) VALUES
(2, 2, 1, 4500.00);

-- Inserindo os dois novos itens na nova venda
INSERT INTO itens_venda (venda_id, produto_id, quantidade, preco_unitario) VALUES
(3, 5, 1, 350.00),
(3, 6, 1, 299.90);