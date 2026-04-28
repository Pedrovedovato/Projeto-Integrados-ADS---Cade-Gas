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
    <title>Cadastro - CadêGás</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="auth-container">
    <div class="auth-card">
        <h1>Cadastro</h1>
        <p class="subtitle">Crie a sua conta</p>

        <form id="registerForm">
            <div class="form-group">
                <label for="nome">Nome completo</label>
                <input type="text" id="nome" name="nome" required>
            </div>

            <div class="form-group">
                <label for="telefone">Telefone</label>
                <input type="tel" id="telefone" name="telefone" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="endereco">Endereço</label>
                <input type="text" id="endereco" name="endereco" required>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="cidade">Cidade</label>
                    <input type="text" id="cidade" name="cidade" required>
                </div>

                <div class="form-group">
                    <label for="estado">Estado</label>
                    <input type="text" id="estado" name="estado" maxlength="2" required>
                </div>
            </div>

            <div class="form-group">
                <label for="cep">CEP</label>
                <input type="text" id="cep" name="cep" required>
            </div>

            <div class="form-group">
                <label for="senha">Senha</label>
                <input type="password" id="senha" name="senha" required>
            </div>

            <div id="errorMessage" class="error-message"></div>

            <button type="submit" class="btn btn-primary btn-full">Cadastrar</button>
        </form>

        <p class="auth-link">
            Já tem conta? <a href="login.php">Entre aqui</a>
        </p>
    </div>
</div>

<script>
    document.getElementById('registerForm').addEventListener('submit', async (e) => {
        e.preventDefault();

        const formData = {
            nome: document.getElementById('nome'.value,
            telefone: document.getElementById('telefone').value,
            email: document.getElementById('email').value,
            endereco: document.getElementById('endereco').value,
            cidade: document.getElementById('cidade').value,
            estado: document.getElementById('estado').value,
            cep: document.getElementById('cep').value,
            senha: document.getElementById('senha').value
        };

        const errorDiv = document.getElementById('errorMessage');

        try {
            const response = await fetch('../../backend/public/routes.php/api/auth/register', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });

            const data = await response.json();

            if (data.success) {
                alert('Cadastro realizado com sucesso!');
                window.location.href = 'login.php';
            } else {
                errorDiv.textContent = data.error || 'Erro ao fazer cadastro';
            }
        } catch (error) {
            errorDiv.textContent = 'Erro ao conectar com servidor';
        }
    });
</script>
</body>
</html>
