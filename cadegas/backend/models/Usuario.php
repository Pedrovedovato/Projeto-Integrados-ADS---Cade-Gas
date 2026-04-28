<?php
// backend/models/Usuario.php
//Modelo para representar os usuários. Centraliza a lógica de acesso aos dados dos usuários, facilitando a manutenção e organização do código.

require_once __DIR__ . '/../config/database.php';

class Usuario
{
    private $conn;

    public function __construct()
    {
        $this->conn = Database::connect();
    }

    /**
     * Cria um novo usuário no sistema
     */
    public function criar($nome, $email, $telefone, $senha)
    {
        $hashSenha = password_hash($senha, PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuario (nome, email, telefone, senha)
                VALUES (:nome, :email, :telefone, :senha)";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nome', $nome);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':telefone', $telefone);
        $stmt->bindParam(':senha', $hashSenha);

        return $stmt->execute();
    }

    /**
     * Verifica se já existe usuário com esse e-mail
     */
    public function emailExiste($email)
    {
        $sql = "SELECT id_usuario FROM usuario WHERE email = :email";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }
    /**
     * Busca usuário pelo e-mail
     */
    public function buscarPorEmail($email)
    {
        $sql = "SELECT *
                FROM usuario
                WHERE email = :email
                LIMIT 1";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}