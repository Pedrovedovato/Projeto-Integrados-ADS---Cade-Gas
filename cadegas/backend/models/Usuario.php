<?php
// backend/models/Usuario.php
// Modelo dos usuários (consumidores). Centraliza acesso a dados.

require_once __DIR__ . '/../config/database.php';

class Usuario
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::connect();
    }

    /**
     * Cria um novo usuário e retorna o ID gerado (ou null em falha).
     * Endereço é opcional (US03 — pode ser informado depois).
     */
    public function criar($nome, $email, $telefone, $senha, $endereco = null, $cidade = null, $estado = null, $cep = null)
    {
        $hashSenha = password_hash($senha, PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuario
                  (nome, email, telefone, senha, endereco, cidade, estado, cep)
                VALUES
                  (:nome, :email, :telefone, :senha, :endereco, :cidade, :estado, :cep)";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':nome',     $nome,     PDO::PARAM_STR);
            $stmt->bindValue(':email',    $email,    PDO::PARAM_STR);
            $stmt->bindValue(':telefone', $telefone, PDO::PARAM_STR);
            $stmt->bindValue(':senha',    $hashSenha, PDO::PARAM_STR);
            $stmt->bindValue(':endereco', $endereco, $endereco === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':cidade',   $cidade,   $cidade   === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':estado',   $estado,   $estado   === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->bindValue(':cep',      $cep,      $cep      === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
            $stmt->execute();

            return (int) $this->conn->lastInsertId();
        } catch (PDOException $e) {
            error_log('[Usuario::criar] ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Verifica se já existe usuário com esse e-mail
     */
    public function emailExiste($email)
    {
        try {
            $sql = "SELECT id_usuario FROM usuario WHERE email = :email";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log('[Usuario::emailExiste] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca usuário pelo e-mail (linha completa, ou null)
     */
    public function buscarPorEmail($email)
    {
        try {
            $sql = "SELECT * FROM usuario WHERE email = :email LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ?: null;
        } catch (PDOException $e) {
            error_log('[Usuario::buscarPorEmail] ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Verifica se um id_usuario existe (auth fraca do MVP — evita pedido órfão).
     */
    public function existe($idUsuario)
    {
        try {
            $sql = "SELECT 1 FROM usuario WHERE id_usuario = :id LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', (int) $idUsuario, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchColumn() !== false;
        } catch (PDOException $e) {
            error_log('[Usuario::existe] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Busca usuário completo pelo ID para exibir perfil
     * GET /usuarios/{id}
     */
    public function buscarPorId($idUsuario)
    {
        try {
            $sql = "SELECT 
                        id_usuario,
                        nome,
                        email,
                        telefone,
                        endereco,
                        cidade,
                        estado,
                        cep
                    FROM usuario 
                    WHERE id_usuario = :id LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', (int) $idUsuario, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$row) {
                return null;
            }
            
            // Converter id_usuario para inteiro
            $row['id_usuario'] = (int) $row['id_usuario'];
            
            // Garantir que campos nulos sejam strings vazias
            $row['telefone'] = $row['telefone'] ?? '';
            $row['endereco'] = $row['endereco'] ?? '';
            $row['cidade'] = $row['cidade'] ?? '';
            $row['estado'] = $row['estado'] ?? '';
            $row['cep'] = $row['cep'] ?? '';
            
            return $row;
        } catch (PDOException $e) {
            error_log('[Usuario::buscarPorId] ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Busca o endereço cadastrado do usuário (snapshot para o pedido).
     */
    public function buscarEnderecoFormatado($idUsuario)
    {
        try {
            $sql = "SELECT endereco, cidade, estado FROM usuario WHERE id_usuario = :id LIMIT 1";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', (int) $idUsuario, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row || empty($row['endereco'])) {
                return null;
            }
            $partes = array_filter([
                $row['endereco'],
                $row['cidade'] ? $row['cidade'] . ($row['estado'] ? '/' . $row['estado'] : '') : null,
            ]);
            return implode(' — ', $partes);
        } catch (PDOException $e) {
            error_log('[Usuario::buscarEnderecoFormatado] ' . $e->getMessage());
            return null;
        }
    }
}
