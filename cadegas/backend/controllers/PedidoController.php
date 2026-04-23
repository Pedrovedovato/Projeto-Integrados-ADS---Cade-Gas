<?php
//Mock teste controler x rotas

class PedidoController {

    public function criar() {
        echo json_encode([
            "endpoint" => "POST /pedidos",
            "pedido_id" => 123,
            "status" => "Pedido realizado",
            "mensagem" => "Pedido criado com sucesso (mock)"
        ]);
    }

    public function confirmar($pedidoId) {
        echo json_encode([
            "endpoint" => "GET /pedidos/{id}",
            "pedido_id" => $pedidoId,
            "status" => "Pedido realizado",
            "mensagem" => "O distribuidor entrará em contato para confirmar a entrega"
        ]);
    }
}