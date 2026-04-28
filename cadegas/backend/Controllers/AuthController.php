<?php

namespace Cadegas\Controllers;

use Cadegas\Models\Usuario;

// backend/controllers/AuthController.php

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
    /**
     * POST /login
     */
    public function login()
    {
        $data = json_decode(file_get_contents("php://input"), true);

        // Validação básica
        if (
            empty($data['email']) ||
            empty($data['senha'])
        ) {
            http_response_code(400);
            echo json_encode([
                "erro" => "E-mail e senha são obrigatórios"
            ]);
            return;
        }

        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->buscarPorEmail($data['email']);

        // Usuário não encontrado
        if (!$usuario) {
            http_response_code(401);
            echo json_encode([
                "erro" => "E-mail ou senha inválidos"
            ]);
            return;
        }

        // Verifica senha
        if (!password_verify($data['senha'], $usuario['senha'])) {
            http_response_code(401);
            echo json_encode([
                "erro" => "E-mail ou senha inválidos"
            ]);
            return;
        }

        // Login bem-sucedido
        http_response_code(200);
        echo json_encode([
            "mensagem" => "Login realizado com sucesso",
            "usuario" => [
                "id" => $usuario['id_usuario'],
                "nome" => $usuario['nome'],
                "email" => $usuario['email']
            ]
        ]);
    }
}