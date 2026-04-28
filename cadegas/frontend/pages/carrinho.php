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
    <title>Carrinho - CadêGás</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="page-container">
    <header class="page-header">
        <h1>Carrinho</h1>
    </header>

    <div id="cartContent"></div>

    <nav class="bottom-nav">
        <a href="home.php" class="nav-item">
            <span>🏠</span>
            <span>Início</span>
        </a>
        <a href="carrinho.php" class="nav-item active">
            <span>🛒</span>
            <span>Carrinho</span>
            <span id="cartBadge" class="badge"></span>
        </a>
    </nav>
</div>

<script>
    function renderCart() {
        const cart = JSON.parse(localStorage.getItem('cart') || '[]');
        const container = document.getElementById('cartContent');

        if (cart.length === 0) {
            container.innerHTML = `
                    <div class="empty-cart">
                        <p>O seu carrinho está vazio</p>
                        <a href="home.php" class="btn btn-primary">Ver Distribuidores</a>
                    </div>
                `;
            return;
        }

        const subtotal = cart.reduce((sum, item) => sum + (item.preco * item.quantidade), 0);

        container.innerHTML = `
                <div class="cart-items">
                    ${cart.map((item, index) => `
                        <div class="cart-item">
                            <div class="item-info">
                                <h3>${item.nome}</h3>
                                <p class="item-price">R$ ${item.preco.toFixed(2)}</p>
                            </div>
                            <div class="item-controls">
                                <button onclick="updateQuantity(${index}, -1)" class="btn-quantity">-</button>
                                <span class="quantity">${item.quantidade}</span>
                                <button onclick="updateQuantity(${index}, 1)" class="btn-quantity">+</button>
                                <button onclick="removeItem(${index})" class="btn-remove">🗑️</button>
                            </div>
                            <p class="item-total">R$ ${(item.preco * item.quantidade).toFixed(2)}</p>
                        </div>
                    `).join('')}
                </div>
                <div class="cart-summary">
                    <div class="summary-row">
                        <span>Subtotal:</span>
                        <span>R$ ${subtotal.toFixed(2)}</span>
                    </div>
                    <button onclick="goToCheckout()" class="btn btn-primary btn-full">Finalizar Pedido</button>
                </div>
            `;
        updateCartBadge();
    }

    function updateQuantity(index, change) {
        let cart = JSON.parse(localStorage.getItem('cart') || '[]');
        cart[index].quantidade += change;

        if (cart[index].quantidade <= 0) {
            cart.splice(index, 1);
        }

        localStorage.setItem('cart', JSON.stringify(cart));
        renderCart();
    }

    function removeItem(index) {
        let cart = JSON.parse(localStorage.getItem('cart') || '[]');
        cart.splice(index, 1);
        localStorage.setItem('cart', JSON.stringify(cart));
        renderCart();
    }

    function goToCheckout() {
        window.location.href = 'checkout.php';
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

    renderCart();
</script>
</body>
</html>
