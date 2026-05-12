<?php
session_start();

header('Content-Type: application/json; charset=UTF-8');

$data = json_decode(file_get_contents('php://input'), true);
$idUsuario = is_array($data) && isset($data['id_usuario']) ? (int) $data['id_usuario'] : 0;

if ($idUsuario <= 0) {
    http_response_code(400);
    echo json_encode(['erro' => 'id_usuario obrigatório']);
    exit;
}

$_SESSION['usuario_id'] = $idUsuario;
http_response_code(204);
