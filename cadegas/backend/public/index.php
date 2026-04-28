<?php
require_once __DIR__ . '/../config.php';

// ==================================
// CONFIGURAÇÕES PARA TESTE / DEBUG
// ==================================

// Exibe erros do PHP
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Define que todas as respostas serão JSON
header('Content-Type: application/json; charset=UTF-8');

// Permite acesso durante testes locais (opcional, mas útil)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Trata requisição OPTIONS (pré-flight de CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ==================================
// DISPARA O SISTEMA DE ROTAS
// ==================================

require_once __DIR__ . '/../routes.php';

