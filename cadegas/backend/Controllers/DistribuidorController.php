<?php

namespace Cadegas\Controllers;

// backend/controllers/DistribuidorController.php

use Cadegas\Models\Distribuidor;
use Cadegas\Models\Produto;

class DistribuidorController
{
    /**
     * GET /distribuidores
     */
    public function listar()
    {
        $model = new Distribuidor();
        $distribuidores = $model->listarAtivos();

        echo json_encode($distribuidores);
    }

    /**
     * GET /distribuidores/{id}/produtos
     */
    public function listarProdutos($distribuidorId)
    {
        $produtoModel = new Produto();
        $produtos = $produtoModel->listarPorDistribuidor($distribuidorId);

        echo json_encode([
            "distribuidor_id" => $distribuidorId,
            "produtos" => $produtos
        ]);
    }
}