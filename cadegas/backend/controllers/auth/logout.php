<?php
/**
 * API de Logout de Usuário
 * Endpoint: POST /api/auth/logout
 * Destrói a sessão do usuário
 */

require_once __DIR__ . '/../../config/config.php';

// Destruir sessão do usuário
session_destroy();

resposta_json([
    'sucesso' => true,
    'mensagem' => 'Logout realizado com sucesso'
]);
