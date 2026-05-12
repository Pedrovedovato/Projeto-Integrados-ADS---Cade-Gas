<?php
// Rotas da API (front controller: public/index.php)
// Cada bloco resolve uma rota e termina com exit.

require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/DistribuidorController.php';
require_once __DIR__ . '/controllers/ProdutoController.php';
require_once __DIR__ . '/controllers/PedidoController.php';
require_once __DIR__ . '/controllers/UsuarioController.php';

$method = $_SERVER['REQUEST_METHOD'];
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Prefixo onde public/index.php é servido (configurável via .env)
$basePath = $_ENV['ROUTES_BASE'] ?? '/cadegas/backend/public';
if ($basePath !== '' && strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
}

if ($uri === '' || $uri === false) {
    $uri = '/';
}
$uri = rtrim($uri, '/');
if ($uri === '') {
    $uri = '/';
}

// ================================
// AUTENTICAÇÃO
// ================================
if ($uri === '/register' && $method === 'POST') {
    (new AuthController())->register();
    exit;
}
if ($uri === '/login' && $method === 'POST') {
    (new AuthController())->login();
    exit;
}

// ================================
// USUÁRIOS
// ================================
if (preg_match('#^/usuarios/(\d+)$#', $uri, $matches) && $method === 'GET') {
    (new UsuarioController())->buscar((int) $matches[1]);
    exit;
}

// ================================
// DISTRIBUIDORES
// ================================
if ($uri === '/distribuidores' && $method === 'GET') {
    (new DistribuidorController())->listar();
    exit;
}
if (preg_match('#^/distribuidores/(\d+)/produtos$#', $uri, $matches) && $method === 'GET') {
    (new DistribuidorController())->listarProdutos((int) $matches[1]);
    exit;
}

// ================================
// PRODUTOS (lista geral, com info do distribuidor)
// ================================
if ($uri === '/produtos' && $method === 'GET') {
    (new ProdutoController())->listarDisponiveis();
    exit;
}

// ================================
// PEDIDOS
// ================================
if ($uri === '/pedidos' && $method === 'POST') {
    (new PedidoController())->criar();
    exit;
}
if (preg_match('#^/pedidos/(\d+)$#', $uri, $matches) && $method === 'GET') {
    (new PedidoController())->buscar((int) $matches[1]);
    exit;
}

// ================================
// 404
// ================================
http_response_code(404);
echo json_encode(['erro' => 'Endpoint não encontrado']);
exit;
