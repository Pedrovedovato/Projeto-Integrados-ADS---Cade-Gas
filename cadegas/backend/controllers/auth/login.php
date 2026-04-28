<?php
/**
 * API de Login de Usuário
 * Endpoint: POST /api/auth/login
 */

require_once __DIR__ . '/../../config/db.php';

$dados = obter_entrada_json();

// Validação dos campos
if (empty($dados['email']) || empty($dados['senha'])) {
    resposta_json(['erro' => 'Email e senha são obrigatórios'], 400);
}

try {
    $bd = obter_bd();

    // Buscar usuário por email
    $stmt = $bd->prepare("
        SELECT id_usuario, nome, email, senha, telefone, endereco, cidade, estado, cep
        FROM usuario
        WHERE email = ? AND ativo = 1
    ");
    $stmt->execute([$dados['email']]);
    $usuario = $stmt->fetch();

    // Verificar se usuário existe e senha está correta
    if (!$usuario || !password_verify($dados['senha'], $usuario['senha'])) {
        resposta_json(['erro' => 'Email ou senha incorretos'], 401);
    }

    // Remover senha do retorno por segurança
    unset($usuario['senha']);

    // Salvar dados do usuário na sessão
    $_SESSION['usuario_id'] = $usuario['id_usuario'];
    $_SESSION['usuario_nome'] = $usuario['nome'];

    resposta_json([
        'sucesso' => true,
        'mensagem' => 'Login realizado com sucesso!',
        'usuario' => $usuario
    ]);

} catch (PDOException $e) {
    resposta_json(['erro' => 'Erro ao fazer login: ' . $e->getMessage()], 500);
}
