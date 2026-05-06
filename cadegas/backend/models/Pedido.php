<?php
// backend/models/Pedido.php
// Modelo dos pedidos. Centraliza acesso a dados.

require_once __DIR__ . '/../config/database.php';

class Pedido
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::connect();
    }

    /**
     * Expõe a conexão para o controller envolver o fluxo em transação.
     */
    public function getConnection()
    {
        return $this->conn;
    }

    /**
     * Cria um pedido completo (todas as colunas obrigatórias do schema)
     * e retorna o id gerado (ou null em falha).
     */
    public function criarPedido($idUsuario, $idDistribuidor, $subtotal, $taxaEntrega, $total, $formaPagamento, $enderecoEntrega = null)
    {
        $sql = "INSERT INTO pedido
                  (id_usuario, id_distribuidor, status, subtotal, taxa_entrega, total, forma_pagamento, endereco_entrega)
                VALUES
                  (:id_usuario, :id_distribuidor, 'pendente', :subtotal, :taxa_entrega, :total, :forma_pagamento, :endereco_entrega)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id_usuario',       (int) $idUsuario,      PDO::PARAM_INT);
        $stmt->bindValue(':id_distribuidor',  (int) $idDistribuidor, PDO::PARAM_INT);
        $stmt->bindValue(':subtotal',         number_format((float) $subtotal,    2, '.', ''), PDO::PARAM_STR);
        $stmt->bindValue(':taxa_entrega',     number_format((float) $taxaEntrega, 2, '.', ''), PDO::PARAM_STR);
        $stmt->bindValue(':total',            number_format((float) $total,       2, '.', ''), PDO::PARAM_STR);
        $stmt->bindValue(':forma_pagamento',  $formaPagamento, PDO::PARAM_STR);
        $stmt->bindValue(':endereco_entrega', $enderecoEntrega, $enderecoEntrega === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->execute();

        return (int) $this->conn->lastInsertId();
    }

    /**
     * Insere um item de pedido (snapshot de preço).
     * Lança PDOException em caso de erro (chamador deve estar em transação).
     */
    public function adicionarItem($idPedido, $idProduto, $quantidade, $precoUnitario)
    {
        $sql = "INSERT INTO itens_pedido (id_pedido, id_produto, quantidade, preco_unitario)
                VALUES (:id_pedido, :id_produto, :quantidade, :preco)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id_pedido',  (int) $idPedido,    PDO::PARAM_INT);
        $stmt->bindValue(':id_produto', (int) $idProduto,   PDO::PARAM_INT);
        $stmt->bindValue(':quantidade', (int) $quantidade,  PDO::PARAM_INT);
        $stmt->bindValue(':preco',      number_format((float) $precoUnitario, 2, '.', ''), PDO::PARAM_STR);
        $stmt->execute();
    }

    /**
     * Busca o produto (com campos relevantes para validação no fluxo de pedido).
     * Retorna array associativo ou null se não existir.
     */
    public function buscarProduto($idProduto)
    {
        try {
            $sql = "SELECT id_produto, id_distribuidor, nome, preco, disponivel
                    FROM produto
                    WHERE id_produto = :id_produto
                    LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id_produto', (int) $idProduto, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                return null;
            }
            return [
                'id_produto'      => (int)   $row['id_produto'],
                'id_distribuidor' => (int)   $row['id_distribuidor'],
                'nome'            => $row['nome'],
                'preco'           => (float) $row['preco'],
                'disponivel'      => (int)   $row['disponivel'],
            ];
        } catch (PDOException $e) {
            error_log('[Pedido::buscarProduto] ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Busca um pedido pelo ID
     */
    public function buscarPedido($idPedido)
    {
        try {
            $sql = "SELECT * FROM pedido WHERE id_pedido = :id_pedido LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id_pedido', (int) $idPedido, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (PDOException $e) {
            error_log('[Pedido::buscarPedido] ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Itens de um pedido — devolve `nome` do produto (rótulo curto)
     * + `descricao` (texto longo) + subtotal já calculado por linha.
     * Tipos numéricos vêm como número no JSON.
     */
    public function buscarItensPedido($idPedido)
    {
        try {
            $sql = "SELECT ip.id_produto,
                           p.nome,
                           p.descricao,
                           ip.quantidade,
                           ip.preco_unitario,
                           (ip.quantidade * ip.preco_unitario) AS subtotal
                    FROM itens_pedido ip
                    JOIN produto p ON p.id_produto = ip.id_produto
                    WHERE ip.id_pedido = :id_pedido";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id_pedido', (int) $idPedido, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map(function ($r) {
                return [
                    'id_produto'     => (int)   $r['id_produto'],
                    'nome'           => $r['nome'],
                    'descricao'      => $r['descricao'],
                    'quantidade'     => (int)   $r['quantidade'],
                    'preco_unitario' => (float) $r['preco_unitario'],
                    'subtotal'       => (float) $r['subtotal'],
                ];
            }, $rows);
        } catch (PDOException $e) {
            error_log('[Pedido::buscarItensPedido] ' . $e->getMessage());
            return [];
        }
    }
}
