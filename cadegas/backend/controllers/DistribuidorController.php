<?php
//Mock teste controler x rotas
class DistribuidorController {

    public function listar() {
        echo json_encode([
            "endpoint" => "GET /distribuidores",
            "dados" => [
                [
                    "id" => 1,
                    "nome" => "Distribuidor Exemplo",
                    "taxa_entrega" => 10.00
                ]
            ]
        ]);
    }

    public function listarProdutos($distribuidorId) {
        echo json_encode([
            "endpoint" => "GET /distribuidores/{id}/produtos",
            "distribuidor_id" => $distribuidorId,
            "produtos" => [
                [
                    "id" => 1,
                    "nome" => "Gás P13",
                    "preco" => 110.00
                ]
            ]
        ]);
    }
}