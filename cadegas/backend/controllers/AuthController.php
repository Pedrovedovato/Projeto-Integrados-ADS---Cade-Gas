<?php
// backend/controllers/AuthController.php

require_once __DIR__ . '/../models/Usuario.php';

class AuthController
{
    /**
     * POST /register
     */
    public function register()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        // Validação básica
        if (
            empty($data['nome']) ||
            empty($data['email']) ||
            empty($data['telefone']) ||
            empty($data['senha'])
        ) {
            http_response_code(400);
            echo json_encode([
                "erro" => "Dados obrigatórios não preenchidos"
            ]);
            return;
        }

        $usuario = new Usuario();

        // Verifica e-mail duplicado
        if ($usuario->emailExiste($data['email'])) {
            http_response_code(409);
            echo json_encode([
                "erro" => "E-mail já cadastrado"
            ]);
            return;
        }

        // Cria usuário
        $sucesso = $usuario->criar(
            $data['nome'],
            $data['email'],
            $data['telefone'],
            $data['senha']
        );

        if ($sucesso) {
            http_response_code(201);
            echo json_encode([
                "mensagem" => "Usuário cadastrado com sucesso"
            ]);
        } else {
            http_response_code(500);
            echo json_encode([
                "erro" => "Erro ao cadastrar usuário"
            ]);
        }
    }
}