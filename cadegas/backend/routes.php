<?php
//Rotas para o aplicativo
//Define todos os endpoints do MVP
//Direciona corretamente cada controller
//Trata URLs com parâmetros ({id})

use Cadegas\Controllers\AuthController;
use Cadegas\Controllers\DistribuidorController;
use Cadegas\Controllers\PedidoController;

//Captura método HTTP e URI
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);


// remove o caminho base do projeto
$basePath = '/backend/public/index.php';
$uri = str_replace($basePath, '', $uri);


//Remove barra final (/login/ vira /login)
$uri = rtrim($uri, '/');


//================================
// ROTAS DE AUTENTICAÇÃO
// ================================

// POST /register
//Permite que um consumidor crie uma conta no sistema para poder usar o aplicativo.
if ($uri === '/register' && $method === 'POST') {
    (new AuthController())->register();
    exit;
}
// POST /login
//Permite que o consumidor acesse o sistema com suas credenciais
if ($uri === '/login' && $method === 'POST') {
    (new AuthController())->login();
    exit;
}

// ================================
// ROTAS DE DISTRIBUIDORES
// ================================
// GET/distribuidores
//Mostra ao consumidor quais distribuidores estão ativos e disponíveis para pedido
if ($uri === '/distribuidores' && $method === 'GET') {
    (new DistribuidorController())->listar();
    exit;
}

// GET /distribuidores/{id}/produtos
//Mostra os produtos de um distribuidor específico, quando o consumidor escolhe um deles
if (preg_match('#^/distribuidores/(\d+)/produtos$#', $uri, $matches) && $method === 'GET') {
    $distribuidorId = $matches[1];
    (new DistribuidorController())->listarProdutos($distribuidorId);
    exit;
}

// ================================
// ROTAS DE PEDIDOS
// ================================

//POST /pedidos
//Permite que o consumidor finalize o pedido
if ($uri === '/pedidos' && $method === 'POST') {
    (new PedidoController())->criar();
    exit;
}

// GET /pedidos/{id}
if (preg_match('#^/pedidos/(\d+)$#', $uri, $matches) && $method === 'GET') {
    $pedidoId = $matches[1];
    (new PedidoController())->buscar($pedidoId);
    exit;
}

// ================================
// ROTA NÃO ENCONTRADA
// ================================

http_response_code(404);
echo json_encode([
    'erro' => 'Endpoint não encontrado'
]);
