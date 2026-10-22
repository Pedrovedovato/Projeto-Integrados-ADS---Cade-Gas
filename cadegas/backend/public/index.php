<?php
require_once __DIR__ . '/../config.php';

// ==================================
// MODO DE ERRO (debug controlado pelo .env)
// ==================================
$debug = ($_ENV['APP_DEBUG'] ?? '0') === '1';
ini_set('display_errors',         $debug ? '1' : '0');
ini_set('display_startup_errors', $debug ? '1' : '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

// ==================================
// HEADERS PADRÃO
// ==================================
header('Content-Type: application/json; charset=UTF-8');
header('X-Content-Type-Options: nosniff');

// CORS controlado pelo .env (CORS_ORIGIN). Default: localhost.
$corsOrigin = $_ENV['CORS_ORIGIN'] ?? 'http://localhost';
header('Access-Control-Allow-Origin: ' . $corsOrigin);
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Vary: Origin');

// Pré-flight CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ==================================
// DISPARA O SISTEMA DE ROTAS
// ==================================
require_once __DIR__ . '/../routes.php';
