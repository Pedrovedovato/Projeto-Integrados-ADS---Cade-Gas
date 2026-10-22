<?php
session_start();

// Se já estiver logado, redireciona para home
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
    <title>CadêGás - Bem-vindo</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="welcome-container">
        <div class="welcome-card">
            <h1>CadêGás</h1>
            <p class="subtitle">Seu aplicativo de entrega de gás e água</p>

            <div class="welcome-options">
                <div class="option-card">
                    <h2>Consumidor</h2>
                    <p>Peça gás e água com praticidade</p>
                    <div class="button-group">
                        <a href="login.php" class="btn btn-primary">Entrar</a>
                        <a href="register.php" class="btn btn-secondary">Cadastrar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
