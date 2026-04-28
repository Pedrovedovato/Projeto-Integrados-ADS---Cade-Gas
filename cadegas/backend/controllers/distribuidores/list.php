<?php
/**
 * API de Listagem de Distribuidores
 * Endpoint: GET /api/distribuidores/list
 * Retorna todos os distribuidores ativos com seus produtos
 */

require_once __DIR__ . '/../../config/db.php';

try {
    $bd = obter_bd();

    // Buscar todos os distribuidores ativos
    $stmt = $bd->query("
        SELECT
            id_distribuidor,
            nome_empresa,
            telefone,
            CONCAT(endereco, ', ', cidade, ' - ', estado) as endereco_completo,
            taxa_entrega,
            ativo
        FROM distribuidor
        WHERE ativo = 1
        ORDER BY nome_empresa
    ");

    $distribuidores = $stmt->fetchAll();

    // Adicionar informações extras para cada distribuidor
    foreach ($distribuidores as &$dist) {
        // Simular avaliação (rating) - pode ser substituído por dados reais
        $dist['rating'] = 4.5 + (rand(0, 4) / 10);

        // Simular status de aberto/fechado - pode implementar lógica de horário real
        $dist['isOpen'] = true;

        // Verificar quais tipos de produtos o distribuidor oferece
        $stmt_produtos = $bd->prepare("
            SELECT DISTINCT
                CASE
                    WHEN nome LIKE '%gás%' OR nome LIKE '%botijão%' OR nome LIKE '%P13%' OR nome LIKE '%P45%' THEN 'gas'
                    WHEN nome LIKE '%água%' OR nome LIKE '%galão%' THEN 'water'
                    ELSE 'gas'
                END as tipo
            FROM produto
            WHERE id_distribuidor = ? AND disponivel = 1
        ");
        $stmt_produtos->execute([$dist['id_distribuidor']]);
        $tipos = $stmt_produtos->fetchAll(PDO::FETCH_COLUMN);

        $dist['offers'] = $tipos;
    }

    resposta_json([
        'sucesso' => true,
        'distribuidores' => $distribuidores
    ]);

} catch (PDOException $e) {
    resposta_json(['erro' => 'Erro ao buscar distribuidores: ' . $e->getMessage()], 500);
}
