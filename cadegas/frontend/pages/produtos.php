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
    <title>Produtos - CadêGás</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="page-container">
    <header class="page-header">
        <h1 id="supplierName">Produtos</h1>
    </header>

    <div id="productsList" class="products-grid"></div>

    <nav class="bottom-nav">
        <a href="home.php" class="nav-item">
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
    const distributorId = localStorage.getItem('selected_distribuidor');

    if (!distributorId) {
        window.location.href = 'home.php';
    }

    async function loadProducts() {
        try {
            const response = await fetch(`../../backend/public/distribuidores/${distributorId}/produtos`);
            const data = await response.json();

            if (data.success) {
                renderProducts(data.produtos);
            }
        } catch (error) {
            console.error('Erro ao carregar produtos:', error);
        }
    }

    function renderProducts(produtos) {
        const container = document.getElementById('productsList');

        if (produtos.length === 0) {
            container.innerHTML = '<p class="empty-message">Nenhum produto disponível</p>';
            return;
        }

        container.innerHTML = produtos.map(produto => `
                <div class="product-card">
                    <div class="product-image">
                        ${produto.category === 'gas' ?
            '<img src="../assets/img/botijao_wide.jpg" alt="Botijão de Gás">' :
            '<img src="../assets/img/imgAgua20L1.jpg" alt="Bombona de Água">'
        }
                    </div>
                    <div class="product-info">
                        <h3>${produto.nome}</h3>
                        <p>${produto.descricao || ''}</p>
                        <p class="product-size">${produto.size}</p>
                        <p class="product-price">R$ ${parseFloat(produto.preco).toFixed(2)}</p>
                        <button class="btn btn-primary btn-full" onclick="addToCart(${produto.id_produto}, '${produto.nome}', ${produto.preco})">
                            Adicionar ao Carrinho
                        </button>
                    </div>
                </div>
            `).join('');
    }

    function addToCart(id, nome, preco) {
        let cart = JSON.parse(localStorage.getItem('cart') || '[]');
        const existingItem = cart.find(item => item.id_produto === id);

        if (existingItem) {
            existingItem.quantidade++;
        } else {
            cart.push({
                id_produto: id,
                nome: nome,
                preco: parseFloat(preco),
                quantidade: 1
            });
        }

        localStorage.setItem('cart', JSON.stringify(cart));
        updateCartBadge();
        alert('Produto adicionado ao carrinho!');
    }

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

    loadProducts();
    updateCartBadge();
</script>
</body>
</html>
