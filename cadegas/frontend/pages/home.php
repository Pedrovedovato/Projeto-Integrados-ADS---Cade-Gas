<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: main.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Distribuidores - CadêGás</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="page-container">
    <header class="page-header">
        <h1>Distribuidores</h1>
        <div class="header-right">
            <div class="user-info">
                <span id="userName"></span>
                <div class="time-badge">🕐 18h - 6h</div>
            </div>
            <button id="logoutBtn" class="btn btn-danger">Sair</button>
        </div>
    </header>

    <div class="filter-container">
        <label for="filter">Filtrar por:</label>
        <select id="filter">
            <option value="all">Todos</option>
            <option value="gas">Gás</option>
            <option value="water">Água</option>
        </select>
    </div>

    <div id="suppliersList" class="suppliers-grid"></div>

    <nav class="bottom-nav">
        <a href="home.php" class="nav-item active">
            <span>🏠</span>
            <span>Início</span>
        </a>
        <a href="carrinho.php" class="nav-item">
            <span>🛒</span>
            <span>Carrinho</span>
            <span id="cartBadge" class="badge"></span>
        </a>
    </nav>
</div>

<script>
    let distribuidores = [];
    let usuario = JSON.parse(localStorage.getItem('usuario') || '{}');

    document.getElementById('userName').textContent = usuario.nome || '';

    async function loadDistribuidores() {
        try {
            const response = await fetch('../../backend/public/distribuidores');
            const data = await response.json();

            if (data.success) {
                distribuidores = data.distribuidores;
                renderDistribuidores();
            }
        } catch (error) {
            console.error('Erro ao carregar distribuidores:', error);
        }
    }

    function renderDistribuidores() {
        const filter = document.getElementById('filter').value;
        const container = document.getElementById('suppliersList');

        const filtered = distribuidores.filter(dist => {
            if (filter === 'all') return true;
            return dist.offers.includes(filter);
        });

        container.innerHTML = filtered.map(dist => `
                <div class="supplier-card" onclick="selectSupplier(${dist.id_distribuidor})">
                    <h3>${dist.nome_empresa}</h3>
                    <p>📞 ${dist.telefone}</p>
                    <p>📍 ${dist.endereco_completo}</p>
                    <p>⭐ ${dist.rating.toFixed(1)}</p>
                    <p class="delivery-fee">Taxa de entrega: R$ ${parseFloat(dist.taxa_entrega).toFixed(2)}</p>
                    <div class="offers">
                        ${dist.offers.map(o => `<span class="badge">${o === 'gas' ? '🔥 Gás' : '💧 Água'}</span>`).join('')}
                    </div>
                </div>
            `).join('');
    }

    function selectSupplier(id) {
        localStorage.setItem('selected_distribuidor', id);
        window.location.href = 'produtos.php';
    }

    document.getElementById('filter').addEventListener('change', renderDistribuidores);

    document.getElementById('logoutBtn').addEventListener('click', async () => {
        await fetch('../../backend/public/logout', { method: 'POST' });
        localStorage.clear();
        window.location.href = 'main.php';
    });

    function updateCartBadge() {
        const cart = JSON.parse(localStorage.getItem('cart') || '[]');
        const total = cart.reduce((sum, item) => sum + item.quantidade, 0);
        const badge = document.getElementById('cartBadge');
        if (total > 0) {
            badge.textContent = total;
            badge.style.display = 'inline';
        } else {
            badge.style.display = 'none';
        }
    }

    loadDistribuidores();
    updateCartBadge();
</script>
</body>
</html>
