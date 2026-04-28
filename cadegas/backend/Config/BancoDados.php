<?php

namespace Cadegas\Config;

use PDO;
use PDOException;

/**
 * Classe de Conexão com Banco de Dados
 * Implementa padrão Singleton para garantir uma única conexão
 */

class BancoDados
{
    private static $instancia = null;
    private $conexao;

    /**
     * Construtor privado - padrão Singleton
     * Cria conexão PDO com MySQL
     */
    private function __construct()
    {
        try {
            $this->conexao = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die("Erro de conexão com banco de dados: " . $e->getMessage());
        }
    }

    /**
     * Retorna instância da conexão
     * @return PDO Objeto de conexão PDO
     */
    public static function obterInstancia()
    {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    /**
     * Retorna objeto de conexão PDO
     * @return PDO
     */
    public function obterConexao()
    {
        return $this->conexao;
    }

    // Prevenir clonagem da instância
    private function __clone()
    {
    }
}
