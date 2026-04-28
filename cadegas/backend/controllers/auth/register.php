<?php
/**
 * API de Cadastro de Usuário
 * Endpoint: POST /api/auth/register
 */

require_once __DIR__ . '/../../config/db.php';

$dados = get_json_input();

// Validação dos campos obrigatórios
$campos_obrigatorios = ['nome', 'email', 'senha', 'telefone', 'endereco', 'cep'];
foreach ($campos_obrigatorios as $campo) {
    if (empty($dados[$campo])) {
        json_response(['erro' => "Campo obrigatório: $campo"], 400);
    }
}

// Validar formato de email
if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
    json_response(['erro' => 'Email inválido'], 400);
}

// Validar tamanho mínimo da senha
if (strlen($dados['senha']) < 6) {
    json_response(['erro' => 'Senha deve ter no mínimo 6 caracteres'], 400);
}

try {
    $bd = get_db();

    // Verificar se email já existe no banco
    $stmt = $bd->prepare("SELECT id_usuario FROM usuario WHERE email = ?");
    $stmt->execute([$dados['email']]);

    if ($stmt->fetch()) {
        json_response(['erro' => 'Email já cadastrado'], 400);
    }

    // Criptografar senha usando bcrypt
    $senha_hash = password_hash($dados['senha'], PASSWORD_BCRYPT);

    // Inserir novo usuário no banco
    $stmt = $bd->prepare("
        INSERT INTO usuario (nome, email, senha, telefone, endereco, cidade, estado, cep)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $dados['nome'],
        $dados['email'],
        $senha_hash,
        $dados['telefone'],
        $dados['endereco'],
        $dados['cidade'] ?? null,
        $dados['estado'] ?? null,
        $dados['cep']
    ]);

    $id_usuario = $bd->lastInsertId();

    // Buscar usuário recém-criado
    $stmt = $bd->prepare("
        SELECT id_usuario, nome, email, telefone, endereco, cidade, estado, cep
        FROM usuario
        WHERE id_usuario = ?
    ");
    $stmt->execute([$id_usuario]);
    $usuario = $stmt->fetch();

    // Salvar dados do usuário na sessão
    $_SESSION['usuario_id'] = $usuario['id_usuario'];
    $_SESSION['usuario_nome'] = $usuario['nome'];

    json_response([
        'sucesso' => true,
        'mensagem' => 'Conta criada com sucesso!',
        'usuario' => $usuario
    ], 201);

} catch (PDOException $e) {
    json_response(['erro' => 'Erro ao criar conta: ' . $e->getMessage()], 500);
}
