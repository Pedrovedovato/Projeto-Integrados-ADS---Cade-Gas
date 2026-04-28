<?php
// backend/models/Pedido.php
//Modelo para representar os pedidos. Centraliza a lógica de acesso aos dados dos pedidos, facilitando a manutenção e organização do código.

require_once __DIR__ . '/../config/database.php';

class Pedido
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::connect();
    }

    /**
     * Cria um pedido e retorna o ID gerado
     */
    public function criarPedido($idUsuario, $idDistribuidor, $total)
    {
        $sql = "INSERT INTO pedido (id_usuario, id_distribuidor, total)
                VALUES (:id_usuario, :id_distribuidor, :total)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_usuario', $idUsuario);
        $stmt->bindParam(':id_distribuidor', $idDistribuidor);
        $stmt->bindParam(':total', $total);
        $stmt->execute();

        return $this->conn->lastInsertId();
    }

    /**
     * Adiciona itens ao pedido
     */
    public function adicionarItem($idPedido, $idProduto, $quantidade, $preco)
    {
        $sql = "INSERT INTO itens_pedido (id_pedido, id_produto, quantidade, preco_unitario)
                VALUES (:id_pedido, :id_produto, :quantidade, :preco)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_pedido', $idPedido);
        $stmt->bindParam(':id_produto', $idProduto);
        $stmt->bindParam(':quantidade', $quantidade);
        $stmt->bindParam(':preco', $preco);
        $stmt->execute();
    }

    /**
     * Busca preço de um produto
     */
    public function buscarPrecoProduto($idProduto)
    {
        $sql = "SELECT preco FROM produto WHERE id_produto = :id_produto";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_produto', $idProduto);
        $stmt->execute();

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado['preco'];
    }
}