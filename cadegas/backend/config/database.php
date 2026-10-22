<?php
// config/database.php
// Conexão PDO singleton — lê credenciais do .env (carregado por config.php).
// Defaults batem com a instalação padrão do UwAmp (root / senha vazia).

class Database
{
    private static $connection = null;

    public static function connect()
    {
        if (self::$connection !== null) {
            return self::$connection;
        }

        $host    = $_ENV['DB_HOST']     ?? 'localhost';
        $dbName  = $_ENV['DB_NAME']     ?? 'cadgas';
        $user    = $_ENV['DB_USER']     ?? 'root';
        $pass    = $_ENV['DB_PASSWORD'] ?? '';
        $port    = $_ENV['DB_PORT']     ?? '3306';
        $charset = 'utf8mb4';

        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset={$charset}";
            self::$connection = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            // Loga internamente; nunca expõe a mensagem do PDO ao cliente.
            error_log('[Database::connect] ' . $e->getMessage());
            http_response_code(500);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(['erro' => 'Erro de conexão com o banco de dados']);
            exit;
        }

        return self::$connection;
    }
}
