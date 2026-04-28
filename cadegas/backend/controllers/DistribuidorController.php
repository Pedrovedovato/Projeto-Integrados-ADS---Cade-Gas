<?php
// backend/controllers/DistribuidorController.php

require_once __DIR__ . '/../models/Distribuidor.php';

class DistribuidorController
{
    public function listar()
    {
        $model = new Distribuidor();
        $distribuidores = $model->listarAtivos();

        echo json_encode($distribuidores);
    }

    public function listarProdutos($distribuidorId)
    {
        // continua mockado por enquanto
        echo json_encode([
            "endpoint" => "GET /distribuidores/{id}/produtos",
            "distribuidor_id" => $distribuidorId,
            "produtos" => []
        ]);
    }
}