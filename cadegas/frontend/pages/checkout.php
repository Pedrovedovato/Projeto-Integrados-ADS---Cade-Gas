<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header('Location: welcome.php');
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
    <script src="../assets/js/api.js"></script>
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
                    <option value="dinheiro">Dinheiro</option>
                    <option value="pix">PIX</option>
                    <option value="cartao">Cartão</option>
                </select>
            </div>

            <div class="section">
                <h2>Endereço de Entrega (opcional)</h2>
                <input type="text" id="enderecoEntrega" class="form-control" placeholder="Se vazio, usará o endereço cadastrado">
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

        // Exibir dados do usuário (se disponíveis)
        const enderecoCompleto = usuario.endereco && usuario.cidade 
            ? `${usuario.endereco}, ${usuario.cidade}/${usuario.estado}` 
            : 'Não cadastrado - informe abaixo';

        document.getElementById('userData').innerHTML = `
            <p><strong>Nome:</strong> ${usuario.nome || 'Não informado'}</p>
            <p><strong>Telefone:</strong> ${usuario.telefone || 'Não informado'}</p>
            <p><strong>Endereço:</strong> ${enderecoCompleto}</p>
            ${usuario.cep ? `<p><strong>CEP:</strong> ${usuario.cep}</p>` : ''}
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
                <p class="info-text">* Taxa de entrega será calculada e adicionada ao total</p>
            `;
        }

        document.getElementById('confirmBtn').addEventListener('click', async () => {
            const paymentMethod = document.getElementById('paymentMethod').value;
            const enderecoEntrega = document.getElementById('enderecoEntrega').value.trim();
            const errorDiv = document.getElementById('errorMessage');

            if (!paymentMethod) {
                errorDiv.textContent = 'Selecione uma forma de pagamento';
                return;
            }

            // swagger-1 espera: {id_usuario, id_distribuidor, itens[{id_produto, quantidade}], forma_pagamento?, endereco_entrega?}
            const pedidoData = {
                id_usuario: usuario.id_usuario,
                id_distribuidor: parseInt(distributorId),
                itens: cart.map(item => ({
                    id_produto: item.id_produto,
                    quantidade: item.quantidade
                    // Backend calcula preco_unitario
                })),
                forma_pagamento: paymentMethod // "dinheiro", "pix" ou "cartao"
            };

            // Adicionar endereço de entrega se informado
            if (enderecoEntrega) {
                pedidoData.endereco_entrega = enderecoEntrega;
            }

            // Usando módulo API baseado em swagger-1.json
            const resultado = await API.Pedidos.criar(pedidoData);

            if (resultado.sucesso) {
                localStorage.removeItem('cart');
                localStorage.removeItem('selected_distribuidor');

                // swagger-1 retorna: {mensagem, id_pedido, subtotal, taxa_entrega, total, forma_pagamento, status}
                const mensagemDetalhada = `
Pedido #${resultado.id_pedido} confirmado!

Subtotal: R$ ${resultado.subtotal.toFixed(2)}
Taxa de Entrega: R$ ${resultado.taxa_entrega.toFixed(2)}
Total: R$ ${resultado.total.toFixed(2)}

Forma de Pagamento: ${resultado.forma_pagamento}
Status: ${resultado.status}

O distribuidor entrará em contato para confirmar a entrega.
                `.trim();

                alert(mensagemDetalhada);
                window.location.href = 'home.php';
            } else {
                errorDiv.textContent = resultado.erro || 'Erro ao criar pedido';
            }
        });

        renderOrderSummary();
    </script>
</body>
</html>
