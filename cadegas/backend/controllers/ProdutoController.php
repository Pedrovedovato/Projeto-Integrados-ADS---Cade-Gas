<?php
// backend/controllers/ProdutoController.php

require_once __DIR__ . '/../models/Produto.php';

class ProdutoController
{
    /**
     * GET /produtos
     * Lista todos os produtos disponíveis com info do distribuidor.
     */
    public function listarDisponiveis()
    {
        $produtoModel = new Produto();
        $produtos = $produtoModel->listarDisponiveis();

        http_response_code(200);
        echo json_encode([
            "produtos" => $produtos,
        ]);
    }
}
