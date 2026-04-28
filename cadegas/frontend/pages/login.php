<?php
session_start();

if (isset($_SESSION['usuario_id'])) {
    header('Location: home.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CadêGás</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="auth-container">
    <div class="auth-card">
        <h1>Login</h1>
        <p class="subtitle">Entre com a sua conta</p>

        <form id="loginForm">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" required>
            </div>

            <div id="errorMessage" class="error-message"></div>

            <button type="submit" class="btn btn-primary btn-full">Entrar</button>
        </form>

        <p class="auth-link">
            Não tem conta? <a href="cadastro.php">Cadastre-se</a>
        </p>
    </div>
</div>

<script>
    document.getElementById('loginForm').addEventListener('submit', async (e) => {
        e.preventDefault();

        const email = document.getElementById('email').value;
        const senha = document.getElementById('senha').value;
        const errorDiv = document.getElementById('errorMessage');

        try {
            const response = await fetch('../../backend/controllers/auth/login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, senha })
            });

            const data = await response.json();

            if (data.success) {
                localStorage.setItem('usuario', JSON.stringify(data.usuario));
                window.location.href = 'home.php';
            } else {
                errorDiv.textContent = data.error || 'Erro ao fazer login';
            }
        } catch (error) {
            errorDiv.textContent = 'Erro ao conectar com servidor';
        }
    });
</script>
</body>
</html>
