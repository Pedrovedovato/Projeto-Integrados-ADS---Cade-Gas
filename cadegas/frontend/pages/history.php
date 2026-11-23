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
    <title>Meus Pedidos - CadêGás</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/api.js"></script>
</head>
<body>
    <div class="page-container">
        <header class="page-header">
            <h1>Meus Pedidos</h1>
        </header>

        <div id="historyContent" class="history-container">
            <p class="empty-message">Carregando pedidos...</p>
        </div>

        <nav class="bottom-nav">
            <a href="home.php" class="nav-item">
                <span>🏠</span>
                <span>Início</span>
            </a>
            <a href="cart.php" class="nav-item">
                <span>🛒</span>
                <span>Carrinho</span>
                <span id="cartBadge" class="badge"></span>
            </a>
            <a href="history.php" class="nav-item active">
                <span>📋</span>
                <span>Pedidos</span>
            </a>
        </nav>
    </div>

    <script>
        const usuario = JSON.parse(localStorage.getItem('usuario') || '{}');

        function escapeHtml(str) {
            if (str === null || str === undefined) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function formatarData(iso) {
            if (!iso) return '';
            const d = new Date(iso.replace(' ', 'T'));
            if (isNaN(d)) return escapeHtml(iso);
            const dd = String(d.getDate()).padStart(2, '0');
            const mm = String(d.getMonth() + 1).padStart(2, '0');
            const hh = String(d.getHours()).padStart(2, '0');
            const mi = String(d.getMinutes()).padStart(2, '0');
            return `${dd}/${mm}/${d.getFullYear()} ${hh}:${mi}`;
        }

        function formatarPagamento(fp) {
            const map = { dinheiro: 'Dinheiro', pix: 'PIX', cartao: 'Cartão' };
            return map[fp] || fp || '';
        }

        function brl(v) {
            return 'R$ ' + parseFloat(v || 0).toFixed(2);
        }

        function renderEmpty(container) {
            container.innerHTML = `
                <div class="history-empty-state">
                    <div class="empty-icon">📋</div>
                    <h3>Você ainda não tem pedidos</h3>
                    <p>Quando você fizer seu primeiro pedido, ele aparecerá aqui.</p>
                    <a href="home.php" class="btn btn-primary">Ver Produtos</a>
                </div>
            `;
        }

        function renderError(container, msg) {
            container.innerHTML = `
                <p class="empty-message">${escapeHtml(msg || 'Erro ao carregar pedidos')}</p>
            `;
        }

        function renderPedidos(container, pedidos) {
            container.innerHTML = pedidos.map(p => {
                const itensRows = (p.itens || []).map(it => `
                    <tr>
                        <td>${it.quantidade}x ${escapeHtml(it.nome)}</td>
                        <td class="item-price">${brl(it.subtotal)}</td>
                    </tr>
                `).join('');

                const statusClass = 'status-' + (p.status || 'pendente');

                return `
                    <article class="history-card">
                        <header class="order-card-header">
                            <div>
                                <h3>Pedido #${p.id_pedido}</h3>
                                <div class="order-meta">${formatarData(p.criado_em)}</div>
                            </div>
                            <span class="order-status-badge ${statusClass}">${escapeHtml(p.status || 'pendente')}</span>
                        </header>

                        <div class="order-card-body">
                            <div class="order-distributor-info">
                                <strong>📦 ${escapeHtml(p.distribuidor_nome || 'Distribuidor')}</strong>
                            </div>

                            ${p.endereco_entrega ? `
                                <div class="order-address-info">
                                    <strong>Entrega:</strong> ${escapeHtml(p.endereco_entrega)}
                                </div>
                            ` : ''}

                            <table class="history-items-table">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th class="item-price">Valor</th>
                                    </tr>
                                </thead>
                                <tbody>${itensRows}</tbody>
                            </table>

                            <div class="history-summary">
                                <div class="history-summary-row">
                                    <span>Subtotal</span>
                                    <span>${brl(p.subtotal)}</span>
                                </div>
                                <div class="history-summary-row">
                                    <span>Taxa de entrega</span>
                                    <span>${brl(p.taxa_entrega)}</span>
                                </div>
                                <div class="history-summary-row">
                                    <span>Pagamento</span>
                                    <span>${escapeHtml(formatarPagamento(p.forma_pagamento))}</span>
                                </div>
                                <div class="history-summary-row total-row">
                                    <span>Total</span>
                                    <span>${brl(p.total)}</span>
                                </div>
                            </div>
                        </div>
                    </article>
                `;
            }).join('');
        }

        async function carregarHistorico() {
            const container = document.getElementById('historyContent');

            if (!usuario.id_usuario) {
                renderError(container, 'Sessão inválida. Faça login novamente.');
                return;
            }

            const resultado = await API.Usuarios.listarPedidos(usuario.id_usuario);

            if (!resultado.sucesso) {
                renderError(container, resultado.erro);
                return;
            }

            const pedidos = resultado.pedidos || [];
            if (pedidos.length === 0) {
                renderEmpty(container);
                return;
            }

            renderPedidos(container, pedidos);
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

        carregarHistorico();
        updateCartBadge();
    </script>
</body>
</html>
