<?php
/**
 * Arquivo de Configuração Principal
 * Contém configurações de banco, sessão e funções auxiliares
 */

// Carregar variáveis de ambiente do arquivo .env
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

// Configurações do banco de dados
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'cadegas');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: 'root');  // UwAmp usa 'root' como senha padrão (pode ser vazio em XAMPP)

// Configurações da aplicação
define('BASE_URL', 'http://localhost');
define('SITE_NAME', 'CadêGás');

// Configurações de segurança de sessão
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);

// Iniciar sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configurar timezone para Brasil
date_default_timezone_set('America/Sao_Paulo');

// Headers CORS (apenas para desenvolvimento - remover em produção)
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json; charset=utf-8');

/**
 * Função auxiliar para obter entrada JSON do request
 * @return array|null Dados decodificados do JSON
 */
function obter_entrada_json() {
    $entrada = file_get_contents('php://input');
    return json_decode($entrada, true);
}

// Manter compatibilidade com código antigo
function get_json_input() {
    return obter_entrada_json();
}

/**
 * Função auxiliar para enviar resposta JSON
 * @param array $dados Dados para retornar
 * @param int $status Código HTTP de status
 */
function resposta_json($dados, $status = 200) {
    http_response_code($status);
    echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// Manter compatibilidade com código antigo
function json_response($dados, $status = 200) {
    resposta_json($dados, $status);
}
