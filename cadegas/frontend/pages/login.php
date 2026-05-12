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
    <script src="../assets/js/api.js"></script>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <h1>Login</h1>
            <p class="subtitle">Entre com sua conta</p>

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
                Não tem conta? <a href="register.php">Cadastre-se</a>
            </p>
        </div>
    </div>

    <script>
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const email = document.getElementById('email').value;
            const senha = document.getElementById('senha').value;
            const errorDiv = document.getElementById('errorMessage');

            // Usando módulo API baseado em swagger-1.json
            const resultado = await API.Auth.login(email, senha);

            if (resultado.sucesso) {
                // swagger-1 retorna: {mensagem, id_usuario, nome, email, usuario: {id, nome, email}}
                // Usar objeto usuario se disponível, senão usar campos da raiz
                const usuarioData = resultado.usuario || {
                    id: resultado.id_usuario,
                    nome: resultado.nome,
                    email: resultado.email
                };

                const usuario = {
                    id_usuario: usuarioData.id || resultado.id_usuario,
                    nome: usuarioData.nome || resultado.nome,
                    email: usuarioData.email || resultado.email,
                    // Nota: swagger-1 não retorna telefone/endereço no login
                    // Esses campos serão solicitados no checkout se necessário
                    telefone: '',
                    endereco: '',
                    cidade: '',
                    estado: '',
                    cep: ''
                };

                localStorage.setItem('usuario', JSON.stringify(usuario));
                window.location.href = 'home.php';
            } else {
                errorDiv.textContent = resultado.erro || 'Erro ao fazer login';
            }
        });
    </script>
</body>
</html>
