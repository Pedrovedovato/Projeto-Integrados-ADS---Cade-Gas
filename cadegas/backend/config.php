<?php

function carregarEnv($caminho) {
    // Popula $_ENV a partir de variáveis de ambiente do sistema/Docker
    foreach (getenv() as $nome => $valor) {
        if (!isset($_ENV[$nome])) {
            $_ENV[$nome] = $valor;
        }
    }
    foreach ($_SERVER as $nome => $valor) {
        if (is_string($valor) && !isset($_ENV[$nome])) {
            $_ENV[$nome] = $valor;
        }
    }

    if (!file_exists($caminho)) {
        return;
    }

    $linhas = file($caminho, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($linhas as $linha) {
        $linha = trim($linha);
        if ($linha === '' || strpos($linha, '#') === 0 || strpos($linha, '=') === false) {
            continue;
        }

        list($nome, $valor) = explode('=', $linha, 2);
        $nome = trim($nome);
        $valor = trim($valor);

        $_ENV[$nome] = $valor;
    }
}

// Carrega o .env
carregarEnv(__DIR__ . '/.env');