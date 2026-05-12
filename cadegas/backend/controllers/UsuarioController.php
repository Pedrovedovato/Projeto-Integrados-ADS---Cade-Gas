<?php
// backend/controllers/UsuarioController.php

require_once __DIR__ . '/../models/Usuario.php';

class UsuarioController
{
    /**
     * GET /usuarios/{id}
     * Busca perfil completo de um usuário
     */
    public function buscar($idUsuario)
    {
        if (!is_int($idUsuario) || $idUsuario <= 0) {
            http_response_code(400);
            echo json_encode(["erro" => "ID de usuário inválido"], JSON_UNESCAPED_UNICODE);
            return;
        }

        $usuario = new Usuario();
        $dados = $usuario->buscarPorId($idUsuario);

        if ($dados === null) {
            http_response_code(404);
            echo json_encode(["erro" => "Usuário não encontrado"], JSON_UNESCAPED_UNICODE);
            return;
        }

        http_response_code(200);
        echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
