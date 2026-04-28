<?php

namespace Cadegas\Controllers;

use Cadegas\Models\Pedido;

// backend/controllers/PedidoController.php

class PedidoController
{
    /**
     * POST /pedidos
     */
    public function criar()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        // Validação básica
        if (
            empty($data['id_usuario']) ||
            empty($data['id_distribuidor']) ||
            empty($data['itens'])
        ) {
            http_response_code(400);
            echo json_encode([
                "erro" => "Dados obrigatórios não preenchidos"
            ]);
            return;
        }

        $pedidoModel = new Pedido();
        $total = 0;

        // Calcula total
        foreach ($data['itens'] as $item) {
            $preco = $pedidoModel->buscarPrecoProduto($item['id_produto']);
            $total += $preco * $item['quantidade'];
        }

        // Cria pedido
        $idPedido = $pedidoModel->criarPedido(
            $data['id_usuario'],
            $data['id_distribuidor'],
            $total
        );

        // Cria itens do pedido
        foreach ($data['itens'] as $item) {
            $preco = $pedidoModel->buscarPrecoProduto($item['id_produto']);
            $pedidoModel->adicionarItem(
                $idPedido,
                $item['id_produto'],
                $item['quantidade'],
                $preco
            );
        }

        http_response_code(201);
        echo json_encode([
            "mensagem" => "Pedido criado com sucesso",
            "id_pedido" => $idPedido,
            "total" => $total
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
            echo json_encode([
                "erro" => "Pedido não encontrado"
            ]);
            return;
        }

        $itens = $pedidoModel->buscarItensPedido($idPedido);

        http_response_code(200);
        echo json_encode([
            "pedido" => [
                "id_pedido" => $pedido['id_pedido'],
                "id_usuario" => $pedido['id_usuario'],
                "id_distribuidor" => $pedido['id_distribuidor'],
                "total" => $pedido['total'],
                "criado_em" => $pedido['criado_em']
            ],
            "itens" => $itens,
            "mensagem" => "O distribuidor entrará em contato para confirmar a entrega"
        ]);
    }
}