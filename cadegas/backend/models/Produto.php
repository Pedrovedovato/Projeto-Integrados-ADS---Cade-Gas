<?php
// backend/models/Produto.php
// Modelo dos produtos. Centraliza acesso a dados.

require_once __DIR__ . '/../config/database.php';

class Produto
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::connect();
    }

    /**
     * Retorna os produtos disponíveis de um distribuidor.
     * Filtra por disponivel = 1 (US05). Tipos numéricos vêm como número no JSON.
     */
    public function listarPorDistribuidor($distribuidorId)
    {
        try {
            $sql = "SELECT id_produto, id_distribuidor, nome, descricao, preco, disponivel
                    FROM produto
                    WHERE id_distribuidor = :id_distribuidor
                      AND disponivel = 1
                    ORDER BY nome";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id_distribuidor', (int) $distribuidorId, PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map(function ($r) {
                return [
                    'id_produto'      => (int)   $r['id_produto'],
                    'id_distribuidor' => (int)   $r['id_distribuidor'],
                    'nome'            => $r['nome'],
                    'descricao'       => $r['descricao'],
                    'preco'           => (float) $r['preco'],
                    'disponivel'      => (int)   $r['disponivel'],
                ];
            }, $rows);
        } catch (PDOException $e) {
            error_log('[Produto::listarPorDistribuidor] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Lista todos os produtos disponíveis (de qualquer distribuidor ativo).
     * Inclui nome_empresa e taxa_entrega do distribuidor — frontend precisa
     * destes para mostrar a origem e calcular o total no carrinho.
     */
    public function listarDisponiveis()
    {
        try {
            $sql = "SELECT p.id_produto, p.id_distribuidor, p.nome, p.descricao,
                           p.preco, p.disponivel,
                           d.nome_empresa, d.taxa_entrega
                    FROM produto p
                    JOIN distribuidor d ON d.id_distribuidor = p.id_distribuidor
                    WHERE p.disponivel = 1
                      AND d.ativo = 1
                    ORDER BY p.nome, d.nome_empresa";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return array_map(function ($r) {
                return [
                    'id_produto'      => (int)   $r['id_produto'],
                    'id_distribuidor' => (int)   $r['id_distribuidor'],
                    'nome'            => $r['nome'],
                    'descricao'       => $r['descricao'],
                    'preco'           => (float) $r['preco'],
                    'disponivel'      => (int)   $r['disponivel'],
                    'nome_empresa'    => $r['nome_empresa'],
                    'taxa_entrega'    => (float) $r['taxa_entrega'],
                ];
            }, $rows);
        } catch (PDOException $e) {
            error_log('[Produto::listarDisponiveis] ' . $e->getMessage());
            return [];
        }
    }
}
