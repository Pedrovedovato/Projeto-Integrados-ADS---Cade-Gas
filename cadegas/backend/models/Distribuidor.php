<?php
// backend/models/Distribuidor.php
// Modelo dos distribuidores. Centraliza acesso a dados.

require_once __DIR__ . '/../config/database.php';

class Distribuidor
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::connect();
    }

    /**
     * Retorna todos os distribuidores ativos com os campos visíveis ao consumidor.
     * Tipos numéricos vêm como número no JSON (PDO+MySQL devolveria string para DECIMAL).
     */
    public function listarAtivos()
    {
        try {
            $sql = "SELECT id_distribuidor, nome_empresa, cnpj, telefone,
                           endereco, cidade, estado, taxa_entrega, ativo
                    FROM distribuidor
                    WHERE ativo = 1
                    ORDER BY nome_empresa";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map(function ($r) {
                return [
                    'id_distribuidor' => (int)   $r['id_distribuidor'],
                    'nome_empresa'    => $r['nome_empresa'],
                    'cnpj'            => $r['cnpj'],
                    'telefone'        => $r['telefone'],
                    'endereco'        => $r['endereco'],
                    'cidade'          => $r['cidade'],
                    'estado'          => $r['estado'],
                    'taxa_entrega'    => (float) $r['taxa_entrega'],
                    'ativo'           => (int)   $r['ativo'],
                ];
            }, $rows);
        } catch (PDOException $e) {
            error_log('[Distribuidor::listarAtivos] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca distribuidor por id (campos relevantes para o fluxo de pedido).
     * Retorna null se não existir.
     */
    public function buscarPorId($idDistribuidor)
    {
        try {
            $sql = "SELECT id_distribuidor, nome_empresa, taxa_entrega, ativo
                    FROM distribuidor
                    WHERE id_distribuidor = :id
                    LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', (int) $idDistribuidor, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                return null;
            }
            return [
                'id_distribuidor' => (int)   $row['id_distribuidor'],
                'nome_empresa'    => $row['nome_empresa'],
                'taxa_entrega'    => (float) $row['taxa_entrega'],
                'ativo'           => (int)   $row['ativo'],
            ];
        } catch (PDOException $e) {
            error_log('[Distribuidor::buscarPorId] ' . $e->getMessage());
            return null;
        }
    }
}
