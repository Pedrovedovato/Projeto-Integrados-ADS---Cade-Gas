<?php
/**
 * API de Criação de Pedido
 * Endpoint: POST /api/pedidos/create
 * Cria um novo pedido com itens e calcula valores
 */

require_once __DIR__ . '/../../config/db.php';

$dados = obter_entrada_json();

// Validação dos dados obrigatórios
if (empty($dados['id_usuario']) || empty($dados['id_distribuidor']) || empty($dados['itens']) || empty($dados['forma_pagamento'])) {
    resposta_json(['erro' => 'Dados incompletos'], 400);
}

if (count($dados['itens']) === 0) {
    resposta_json(['erro' => 'Pedido deve ter pelo menos um item'], 400);
}

try {
    $bd = obter_bd();
    $bd->beginTransaction(); // Iniciar transação para garantir integridade

    // Buscar taxa de entrega do distribuidor
    $stmt = $bd->prepare("SELECT taxa_entrega FROM distribuidor WHERE id_distribuidor = ?");
    $stmt->execute([$dados['id_distribuidor']]);
    $distribuidor = $stmt->fetch();

    if (!$distribuidor) {
        throw new Exception('Distribuidor não encontrado');
    }

    $taxa_entrega = $distribuidor['taxa_entrega'];
    $subtotal = 0;

    // Calcular subtotal dos itens
    foreach ($dados['itens'] as $item) {
        $subtotal += $item['preco'] * $item['quantidade'];
    }

    $total = $subtotal + $taxa_entrega;

    // Buscar endereço completo do usuário
    $stmt = $bd->prepare("SELECT endereco, cidade, estado FROM usuario WHERE id_usuario = ?");
    $stmt->execute([$dados['id_usuario']]);
    $usuario = $stmt->fetch();

    $endereco_entrega = $usuario['endereco'] . ' — ' . $usuario['cidade'] . '/' . $usuario['estado'];

    // Inserir pedido no banco
    $stmt = $bd->prepare("
        INSERT INTO pedido
        (id_usuario, id_distribuidor, status, subtotal, taxa_entrega, total, forma_pagamento, endereco_entrega, observacao)
        VALUES (?, ?, 'pendente', ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $dados['id_usuario'],
        $dados['id_distribuidor'],
        $subtotal,
        $taxa_entrega,
        $total,
        $dados['forma_pagamento'],
        $endereco_entrega,
        $dados['observacao'] ?? null
    ]);

    $id_pedido = $bd->lastInsertId();

    // Inserir itens do pedido
    $stmt = $bd->prepare("
        INSERT INTO itens_pedido (id_pedido, id_produto, quantidade, preco_unitario)
        VALUES (?, ?, ?, ?)
    ");

    foreach ($dados['itens'] as $item) {
        $stmt->execute([
            $id_pedido,
            $item['id_produto'],
            $item['quantidade'],
            $item['preco']
        ]);
    }

    $bd->commit(); // Confirmar transação

    resposta_json([
        'sucesso' => true,
        'mensagem' => 'Pedido criado com sucesso!',
        'id_pedido' => $id_pedido,
        'total' => $total
    ], 201);

} catch (Exception $e) {
    $bd->rollBack(); // Reverter transação em caso de erro
    resposta_json(['erro' => 'Erro ao criar pedido: ' . $e->getMessage()], 500);
}
