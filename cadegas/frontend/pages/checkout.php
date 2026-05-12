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

        <div class="checkout-container" id="checkoutMain">
            <div class="section">
                <h2>Resumo do pedido</h2>
                <div id="orderSummary"></div>
                <div class="form-group">
                    <label for="paymentMethod">Forma de pagamento</label>
                    <select id="paymentMethod" class="form-control">
                        <option value="">Selecione</option>
                        <option value="dinheiro">Dinheiro</option>
                        <option value="pix">PIX</option>
                        <option value="cartao">Cartão</option>
                    </select>
                </div>
            </div>

            <div class="section">
                <h2>Informações de contato</h2>
                <div id="contactInfo" class="user-data"></div>
            </div>

            <div class="section">
                <h2>Endereço da entrega</h2>
                <input type="text" id="enderecoEntrega" class="form-control" placeholder="Endereço, cidade/UF — CEP">
                <p class="info-text">Pré-preenchido com seu endereço cadastrado — pode editar se quiser entregar em outro local.</p>
            </div>

            <div id="errorMessage" class="error-message"></div>

            <button id="confirmBtn" class="btn btn-primary btn-full">Confirmar Pedido</button>
        </div>

        <div class="checkout-container" id="checkoutSuccess" style="display:none;">
            <div class="section">
                <h2>Pedido confirmado</h2>
                <div id="successDetails"></div>
                <a href="home.php" class="btn btn-primary btn-full">Voltar para Início</a>
            </div>
        </div>
    </div>

    <script>
        let usuario = JSON.parse(localStorage.getItem('usuario') || '{}');
        const distributorId = localStorage.getItem('selected_distribuidor');
        const cart = JSON.parse(localStorage.getItem('cart') || '[]');

        if (cart.length === 0) {
            window.location.href = 'home.php';
        }

        function montarEnderecoString(u) {
            const linha1 = (u.endereco || '').trim();
            const cidade = (u.cidade || '').trim();
            const estado = (u.estado || '').trim();
            const cep = (u.cep || '').trim();

            const localidade = cidade && estado
                ? `${cidade}/${estado}`
                : (cidade || estado);

            const partes = [];
            if (linha1) partes.push(linha1);
            if (localidade) partes.push(localidade);

            let resultado = partes.join(', ');
            if (cep) {
                resultado = resultado
                    ? `${resultado} — CEP ${cep}`
                    : `CEP ${cep}`;
            }
            return resultado;
        }

        function renderContactInfo() {
            document.getElementById('contactInfo').innerHTML = `
                <p><strong>Nome:</strong> ${usuario.nome || 'Não informado'}</p>
                <p><strong>Email:</strong> ${usuario.email || 'Não informado'}</p>
                <p><strong>Telefone:</strong> ${usuario.telefone || 'Não informado'}</p>
            `;
        }

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

        function preencherEnderecoEntrega() {
            const input = document.getElementById('enderecoEntrega');
            const str = montarEnderecoString(usuario);
            if (str) {
                input.value = str;
            }
        }

        async function carregarPerfil() {
            if (!usuario.id_usuario) return;

            const resultado = await API.Usuarios.buscar(usuario.id_usuario);
            if (!resultado.sucesso) {
                console.warn('Falha ao buscar perfil:', resultado.erro);
                return;
            }

            usuario = {
                ...usuario,
                nome: resultado.nome || usuario.nome,
                email: resultado.email || usuario.email,
                telefone: resultado.telefone || '',
                endereco: resultado.endereco || '',
                cidade: resultado.cidade || '',
                estado: resultado.estado || '',
                cep: resultado.cep || ''
            };
            localStorage.setItem('usuario', JSON.stringify(usuario));
        }

        function mostrarSucesso(resultado) {
            document.getElementById('checkoutMain').style.display = 'none';

            document.getElementById('successDetails').innerHTML = `
                <p><strong>Pedido #${resultado.id_pedido}</strong></p>
                <div class="order-item">
                    <span>Subtotal</span>
                    <span>R$ ${resultado.subtotal.toFixed(2)}</span>
                </div>
                <div class="order-item">
                    <span>Taxa de entrega</span>
                    <span>R$ ${resultado.taxa_entrega.toFixed(2)}</span>
                </div>
                <div class="order-total">
                    <strong>Total</strong>
                    <strong>R$ ${resultado.total.toFixed(2)}</strong>
                </div>
                <p><strong>Forma de pagamento:</strong> ${resultado.forma_pagamento}</p>
                <p><strong>Status:</strong> ${resultado.status}</p>
                <p class="info-text">O distribuidor entrará em contato para confirmar a entrega.</p>
            `;

            document.getElementById('checkoutSuccess').style.display = 'block';
        }

        document.getElementById('confirmBtn').addEventListener('click', async () => {
            const paymentMethod = document.getElementById('paymentMethod').value;
            const enderecoEntrega = document.getElementById('enderecoEntrega').value.trim();
            const errorDiv = document.getElementById('errorMessage');
            errorDiv.textContent = '';

            if (!paymentMethod) {
                errorDiv.textContent = 'Selecione uma forma de pagamento';
                return;
            }

            const pedidoData = {
                id_usuario: usuario.id_usuario,
                id_distribuidor: parseInt(distributorId),
                itens: cart.map(item => ({
                    id_produto: item.id_produto,
                    quantidade: item.quantidade
                })),
                forma_pagamento: paymentMethod
            };

            if (enderecoEntrega) {
                pedidoData.endereco_entrega = enderecoEntrega;
            }

            const resultado = await API.Pedidos.criar(pedidoData);

            if (resultado.sucesso) {
                localStorage.removeItem('cart');
                localStorage.removeItem('selected_distribuidor');
                mostrarSucesso(resultado);
            } else {
                errorDiv.textContent = resultado.erro || 'Erro ao criar pedido';
            }
        });

        (async () => {
            renderOrderSummary();
            renderContactInfo();
            await carregarPerfil();
            renderContactInfo();
            preencherEnderecoEntrega();
        })();
    </script>
</body>
</html>
