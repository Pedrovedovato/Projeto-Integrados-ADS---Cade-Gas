<?php
// config/database.php
//Centraliza a conexão com o banco para não precisar de criar conexão em cada controller. Facilita manutenção e organização do código.

class Database
{
    private static $connection = null;

    public static function connect()
    {
        if (self::$connection === null) {
            $host = 'localhost';
            $dbName = 'cadgas';     // nome do banco
            $user = 'root';        // usuário padrão do UwAmp
            $pass = 'root';            // senha padrão (vazia) alterada para 'root' para compatibilidade com MySQL
            $charset = 'utf8mb4';

            try {
                $dsn = "mysql:host=$host;dbname=$dbName;charset=$charset";
                self::$connection = new PDO($dsn, $user, $pass);
                self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die(json_encode([
                    'erro' => 'Erro de conexão com o banco de dados',
                    'detalhes' => $e->getMessage()
                ]));
            }
        }

        return self::$connection;
    }
}