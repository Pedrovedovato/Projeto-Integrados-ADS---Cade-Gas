<?php
/**
 * API de Listagem de Produtos
 * Endpoint: GET /api/produtos/list?id_distribuidor={id}
 * Retorna todos os produtos disponíveis de um distribuidor
 */

require_once __DIR__ . '/../../config/db.php';

$id_distribuidor = $_GET['id_distribuidor'] ?? null;

// Validação do parâmetro obrigatório
if (!$id_distribuidor) {
    resposta_json(['erro' => 'ID do distribuidor é obrigatório'], 400);
}

try {
    $bd = obter_bd();

    // Buscar produtos do distribuidor com informações adicionais
    $stmt = $bd->prepare("
        SELECT
            p.id_produto,
            p.nome,
            p.descricao,
            p.preco,
            p.disponivel,
            d.nome_empresa as distribuidor,
            CASE
                WHEN p.nome LIKE '%gás%' OR p.nome LIKE '%botijão%' OR p.nome LIKE '%P13%' OR p.nome LIKE '%P45%' THEN 'gas'
                WHEN p.nome LIKE '%água%' OR p.nome LIKE '%galão%' THEN 'water'
                ELSE 'gas'
            END as category,
            CASE
                WHEN p.nome LIKE '%P13%' THEN '13kg'
                WHEN p.nome LIKE '%P45%' THEN '45kg'
                WHEN p.nome LIKE '%P2%' THEN '2kg'
                WHEN p.nome LIKE '%20L%' THEN '20L'
                WHEN p.nome LIKE '%10L%' THEN '10L'
                ELSE 'N/A'
            END as size
        FROM produto p
        JOIN distribuidor d ON p.id_distribuidor = d.id_distribuidor
        WHERE p.id_distribuidor = ? AND p.disponivel = 1
        ORDER BY p.nome
    ");

    $stmt->execute([$id_distribuidor]);
    $produtos = $stmt->fetchAll();

    resposta_json([
        'sucesso' => true,
        'produtos' => $produtos
    ]);

} catch (PDOException $e) {
    resposta_json(['erro' => 'Erro ao buscar produtos: ' . $e->getMessage()], 500);
}
