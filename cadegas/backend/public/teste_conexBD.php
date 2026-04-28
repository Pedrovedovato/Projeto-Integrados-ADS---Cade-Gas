<?php
//Provisório para testar conexão com o banco. Depois de testado, pode ser deletado ou mantido para futuros testes.
require_once __DIR__ . '/../config/database.php';

$conn = Database::connect();

echo json_encode([
    'status' => 'Conectado ao banco com sucesso'
]);