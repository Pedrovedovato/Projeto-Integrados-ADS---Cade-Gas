<?php
// backend/models/Distribuidor.php
//Modelo para representar os distribuidores. Centraliza a lógica de acesso aos dados dos distribuidores, facilitando a manutenção e organização do código.

require_once __DIR__ . '/../config/database.php';

class Distribuidor
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::connect();
    }

    /**
     * Retorna todos os distribuidores ativos
     */
    public function listarAtivos()
    {
        $sql = "SELECT id_distribuidor, nome_empresa, endereco, taxa_entrega
                FROM distribuidor
                WHERE ativo = 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}