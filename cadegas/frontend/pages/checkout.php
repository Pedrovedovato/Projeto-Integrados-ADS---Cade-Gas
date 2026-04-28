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
    <title>Finalizar Pedido - CadêGás</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="page-container">
    <header class="page-header">
        <h1>Finalizar Pedido</h1>
    </header>

    <div class="checkout-container">
        <div class="section">
            <h2>Dados do Usuário</h2>
            <div id="userData" class="user-data"></div>
        </div>

        <div class="section">
            <h2>Resumo do Pedido</h2>
            <div id="orderSummary"></div>
        </div>

        <div class="section">
            <h2>Forma de Pagamento</h2>
            <select id="paymentMethod" class="form-control">
                <option value="">Selecione forma de pagamento</option>
                <option value="money">Dinheiro</option>
                <option value="pix">PIX</option>
                <option value="card">Cartão</option>
            </select>
        </div>

        <div class="section">
            <h2>Observações</h2>
            <textarea id="observacao" class="form-control" rows="3" placeholder="Alguma observação sobre o pedido?"></textarea>
        </div>

        <div id="errorMessage" class="error-message"></div>

        <button id="confirmBtn" class="btn btn-primary btn-full">Confirmar Pedido</button>
    </div>
</div>

<script>
    const usuario = JSON.parse(localStorage.getItem('usuario') || '{}');
    const distributorId = localStorage.getItem('selected_distribuidor');
    const cart = JSON.parse(localStorage.getItem('cart') || '[]');

    if (cart.length === 0) {
        window.location.href = 'home.php';
    }

    document.getElementById('userData').innerHTML = `
            <p><strong>Nome:</strong> ${usuario.nome}</p>
            <p><strong>Telefone:</strong> ${usuario.telefone}</p>
            <p><strong>Endereço:</strong> ${usuario.endereco}, ${usuario.cidade}/${usuario.estado}</p>
            <p><strong>CEP:</strong> ${usuario.cep}</p>
        `;

    function renderOrderSummary() {
        const subtotal = cart.reduce((sum, item) => sum + (item.preco * item.quantidade), 0);

        document.getElementById('orderSummary').innerHTML = `
                <div class="order-items">
                    ${cart.map(item => `
                        <div class="order-item">
                            <span>${item.quantidade}x ${item.nome}</span>
                            <span>R$ ${(item.preco * item.quantidade).toFixed(2)}</span>
                        </div>
                    `).join('')}
                </div>
                <div class="order-total">
                    <strong>Subtotal:</strong>
                    <strong>R$ ${subtotal.toFixed(2)}</strong>
                </div>
                <p class="info-text">* Taxa de entrega será adicionada ao total</p>
            `;
    }

    document.getElementById('confirmBtn').addEventListener('click', async () => {
        const paymentMethod = document.getElementById('paymentMethod').value;
        const observacao = document.getElementById('observacao').value;
        const errorDiv = document.getElementById('errorMessage');

        if (!paymentMethod) {
            errorDiv.textContent = 'Selecione uma forma de pagamento';
            return;
        }

        const pedidoData = {
            id_usuario: usuario.id_usuario,
            id_distribuidor: distributorId,
            itens: cart.map(item => ({
                id_produto: item.id_produto,
                quantidade: item.quantidade,
                preco: item.preco
            })),
            forma_pagamento: paymentMethod,
            observacao: observacao
        };

        try {
            const response = await fetch('../../backend/controllers/pedidos/create.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(pedidoData)
            });

            const data = await response.json();

            if (data.success) {
                localStorage.removeItem('cart');
                localStorage.removeItem('selected_distribuidor');
                alert('Pedido confirmado! O distribuidor entrará em contato para confirmar a entrega');
                window.location.href = 'home.php';
            } else {
                errorDiv.textContent = data.error || 'Erro ao criar pedido';
            }
        } catch (error) {
            errorDiv.textContent = 'Erro ao conectar com servidor';
        }
    });

    renderOrderSummary();
</script>
</body>
</html>
