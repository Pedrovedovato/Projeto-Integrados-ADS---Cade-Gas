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
        if (!is_array($data)) {
            $data = [];
        }

        $nome     = isset($data['nome'])     ? trim((string) $data['nome'])     : '';
        $email    = isset($data['email'])    ? trim((string) $data['email'])    : '';
        $telefone = isset($data['telefone']) ? trim((string) $data['telefone']) : '';
        $senha    = isset($data['senha'])    ? (string) $data['senha']          : '';

        if ($nome === '' || $email === '' || $telefone === '' || $senha === '') {
            http_response_code(400);
            echo json_encode(["erro" => "Dados obrigatórios não preenchidos"]);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(["erro" => "E-mail inválido"]);
            return;
        }

        if (strlen($senha) < 6) {
            http_response_code(400);
            echo json_encode(["erro" => "Senha deve ter no mínimo 6 caracteres"]);
            return;
        }

        // Endereço opcional — US03 (informar localização). Se ausente, fica NULL.
        $endereco = isset($data['endereco']) && trim((string) $data['endereco']) !== '' ? trim((string) $data['endereco']) : null;
        $cidade   = isset($data['cidade'])   && trim((string) $data['cidade'])   !== '' ? trim((string) $data['cidade'])   : null;
        $estado   = isset($data['estado'])   && trim((string) $data['estado'])   !== '' ? trim((string) $data['estado'])   : null;
        $cep      = isset($data['cep'])      && trim((string) $data['cep'])      !== '' ? trim((string) $data['cep'])      : null;

        $usuario = new Usuario();

        if ($usuario->emailExiste($email)) {
            http_response_code(409);
            echo json_encode(["erro" => "E-mail já cadastrado"]);
            return;
        }

        $idUsuario = $usuario->criar($nome, $email, $telefone, $senha, $endereco, $cidade, $estado, $cep);

        if ($idUsuario === null) {
            http_response_code(500);
            echo json_encode(["erro" => "Erro ao cadastrar usuário"]);
            return;
        }

        http_response_code(201);
        echo json_encode([
            "mensagem"   => "Usuário cadastrado com sucesso",
            "id_usuario" => $idUsuario,
            "email"      => $email,
        ]);
    }

    /**
     * POST /login
     */
    public function login()
    {
        $data = json_decode(file_get_contents("php://input"), true);
        if (!is_array($data)) {
            $data = [];
        }

        $email = isset($data['email']) ? trim((string) $data['email']) : '';
        $senha = isset($data['senha']) ? (string) $data['senha']        : '';

        if ($email === '' || $senha === '') {
            http_response_code(400);
            echo json_encode(["erro" => "E-mail e senha são obrigatórios"]);
            return;
        }

        $usuarioModel = new Usuario();
        $usuario = $usuarioModel->buscarPorEmail($email);

        if (!$usuario || !password_verify($senha, $usuario['senha'])) {
            // Mesma mensagem para "não existe" e "senha errada" — não revela se o e-mail está cadastrado.
            http_response_code(401);
            echo json_encode(["erro" => "E-mail ou senha inválidos"]);
            return;
        }

        http_response_code(200);
        echo json_encode([
            "mensagem"   => "Login realizado com sucesso",
            "id_usuario" => (int) $usuario['id_usuario'],
            "nome"       => $usuario['nome'],
            "email"      => $usuario['email'],
            // Mantido para compatibilidade com clientes antigos
            "usuario"    => [
                "id"    => (int) $usuario['id_usuario'],
                "nome"  => $usuario['nome'],
                "email" => $usuario['email'],
            ],
        ]);
    }
}
