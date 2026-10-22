<?php
// Smoke test de conexão com o banco.
// Servido direto pelo Apache (arquivo real → bypassa o front controller).

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json; charset=UTF-8');

try {
    $pdo = Database::connect();
    $pdo->query('SELECT 1')->fetch();
    echo json_encode(['status' => 'Conectado ao banco com sucesso']);
} catch (Throwable $e) {
    error_log('[teste_conexBD] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['erro' => 'Falha ao conectar ao banco']);
}
