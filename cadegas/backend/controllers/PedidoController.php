<?php
// backend/controllers/PedidoController.php

require_once __DIR__ . '/../models/Pedido.php';
require_once __DIR__ . '/../models/Distribuidor.php';
require_once __DIR__ . '/../models/Usuario.php';

class PedidoController
{
    private const FORMAS_PAGAMENTO_VALIDAS = ['dinheiro', 'pix', 'cartao'];

    /**
     * POST /pedidos
     */
    public function criar()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!is_array($data)) {
            $data = [];
        }

        $idUsuario      = isset($data['id_usuario'])      ? (int) $data['id_usuario']      : 0;
        $idDistribuidor = isset($data['id_distribuidor']) ? (int) $data['id_distribuidor'] : 0;
        $itens          = isset($data['itens']) && is_array($data['itens']) ? $data['itens'] : [];

        if ($idUsuario <= 0 || $idDistribuidor <= 0 || empty($itens)) {
            http_response_code(400);
            echo json_encode(["erro" => "Dados obrigatórios não preenchidos"]);
            return;
        }

        // Auth fraca do MVP: garantir que o id_usuario informado existe.
        $usuarioModel = new Usuario();
        if (!$usuarioModel->existe($idUsuario)) {
            http_response_code(401);
            echo json_encode(["erro" => "Usuário não autenticado"]);
            return;
        }

        // Distribuidor precisa existir e estar ativo (US04 / US15).
        $distribuidorModel = new Distribuidor();
        $distribuidor = $distribuidorModel->buscarPorId($idDistribuidor);
        if (!$distribuidor) {
            http_response_code(404);
            echo json_encode(["erro" => "Distribuidor não encontrado"]);
            return;
        }
        if ((int) $distribuidor['ativo'] !== 1) {
            http_response_code(409);
            echo json_encode(["erro" => "Distribuidor indisponível"]);
            return;
        }

        $pedidoModel = new Pedido();

        // Valida forma de pagamento (US08 — pagamento sempre na entrega).
        $formaPagamento = isset($data['forma_pagamento']) ? strtolower(trim((string) $data['forma_pagamento'])) : 'dinheiro';
        if ($formaPagamento === '') {
            $formaPagamento = 'dinheiro';
        }
        if (!in_array($formaPagamento, self::FORMAS_PAGAMENTO_VALIDAS, true)) {
            http_response_code(400);
            echo json_encode(["erro" => "Forma de pagamento inválida"]);
            return;
        }

        // Endereço de entrega: se ausente, snapshot do endereço do usuário.
        $enderecoEntrega = isset($data['endereco_entrega']) && trim((string) $data['endereco_entrega']) !== ''
            ? trim((string) $data['endereco_entrega'])
            : $usuarioModel->buscarEnderecoFormatado($idUsuario);

        // Validação dos itens — uma busca por produto, com snapshot de preço imediato.
        $itensValidados = [];
        $subtotal = 0.0;

        foreach ($itens as $item) {
            $idProduto  = isset($item['id_produto']) ? (int) $item['id_produto'] : 0;
            $quantidade = isset($item['quantidade']) ? (int) $item['quantidade'] : 0;

            if ($idProduto <= 0 || $quantidade <= 0) {
                http_response_code(400);
                echo json_encode(["erro" => "Item inválido (id_produto e quantidade devem ser inteiros positivos)"]);
                return;
            }

            $produto = $pedidoModel->buscarProduto($idProduto);
            if (!$produto) {
                http_response_code(404);
                echo json_encode(["erro" => "Produto $idProduto não encontrado"]);
                return;
            }
            if ((int) $produto['disponivel'] !== 1) {
                http_response_code(409);
                echo json_encode(["erro" => "Produto '{$produto['nome']}' indisponível"]);
                return;
            }
            if ((int) $produto['id_distribuidor'] !== $idDistribuidor) {
                http_response_code(400);
                echo json_encode(["erro" => "Produto '{$produto['nome']}' não pertence ao distribuidor informado"]);
                return;
            }

            $preco = (float) $produto['preco'];
            $subtotal += $preco * $quantidade;
            $itensValidados[] = [
                'id_produto'     => $idProduto,
                'quantidade'     => $quantidade,
                'preco_unitario' => $preco,
            ];
        }

        $taxaEntrega = (float) $distribuidor['taxa_entrega'];
        $total = $subtotal + $taxaEntrega;

        // Transação: pedido + itens são atômicos.
        $conn = $pedidoModel->getConnection();
        try {
            $conn->beginTransaction();

            $idPedido = $pedidoModel->criarPedido(
                $idUsuario,
                $idDistribuidor,
                $subtotal,
                $taxaEntrega,
                $total,
                $formaPagamento,
                $enderecoEntrega
            );

            foreach ($itensValidados as $item) {
                $pedidoModel->adicionarItem(
                    $idPedido,
                    $item['id_produto'],
                    $item['quantidade'],
                    $item['preco_unitario']
                );
            }

            $conn->commit();
        } catch (PDOException $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            error_log('[PedidoController::criar] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(["erro" => "Erro ao criar pedido"]);
            return;
        }

        http_response_code(201);
        echo json_encode([
            "mensagem"        => "Pedido criado com sucesso",
            "id_pedido"       => $idPedido,
            "subtotal"        => round($subtotal, 2),
            "taxa_entrega"    => round($taxaEntrega, 2),
            "total"           => round($total, 2),
            "forma_pagamento" => $formaPagamento,
            "status"          => "pendente",
        ]);
    }

    /**
     * GET /pedidos/{id}
     */
    public function buscar($idPedido)
    {
        $pedidoModel = new Pedido();
        $pedido = $pedidoModel->buscarPedido($idPedido);

        if (!$pedido) {
            http_response_code(404);
            echo json_encode(["erro" => "Pedido não encontrado"]);
            return;
        }

        $itens = $pedidoModel->buscarItensPedido($idPedido);

        http_response_code(200);
        echo json_encode([
            "pedido" => [
                "id_pedido"        => (int) $pedido['id_pedido'],
                "id_usuario"       => (int) $pedido['id_usuario'],
                "id_distribuidor"  => (int) $pedido['id_distribuidor'],
                "status"           => $pedido['status'],
                "subtotal"         => (float) $pedido['subtotal'],
                "taxa_entrega"     => (float) $pedido['taxa_entrega'],
                "total"            => (float) $pedido['total'],
                "forma_pagamento"  => $pedido['forma_pagamento'],
                "endereco_entrega" => $pedido['endereco_entrega'],
                "criado_em"        => $pedido['criado_em'],
            ],
            "itens"    => $itens,
            "mensagem" => "O distribuidor entrará em contato para confirmar a entrega",
        ]);
    }
}
