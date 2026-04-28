<?php
// backend/models/Produto.php
//Modelo para representar os produtos. Centraliza a lógica de acesso aos dados dos produtos, facilitando a manutenção e organização do código.


require_once __DIR__ . '/../config/database.php';

class Produto
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::connect();
    }

    /**
     * Retorna todos os produtos de um distribuidor específico
     */
    public function listarPorDistribuidor($distribuidorId)
    {
        $sql = "SELECT *
                FROM produto
                WHERE id_distribuidor = :id_distribuidor";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_distribuidor', $distribuidorId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
