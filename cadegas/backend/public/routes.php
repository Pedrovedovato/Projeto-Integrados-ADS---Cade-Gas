<?php
require_once __DIR__ . '/../config/config.php';

// Router simples para APIs
$request_uri = $_SERVER['REQUEST_URI'];
$request_method = $_SERVER['REQUEST_METHOD'];

// Remover query string
$uri = strtok($request_uri, '?');

// Rotas da API
$routes = [
    'POST /api/auth/register' => '../controllers/auth/register.php',
    'POST /api/auth/login' => '../controllers/auth/login.php',
    'POST /api/auth/logout' => '../controllers/auth/logout.php',
    'GET /api/distribuidores/list' => '../controllers/distribuidores/list.php',
    'GET /api/produtos/list' => '../controllers/produtos/list.php',
    'POST /api/pedidos/create' => '../controllers/pedidos/create.php',
];

$route_key = $request_method . ' ' . $uri;

if (isset($routes[$route_key])) {
    require_once __DIR__ . '/' . $routes[$route_key];
} else {
    http_response_code(404);
    json_response(['error' => 'Rota não encontrada'], 404);
}
