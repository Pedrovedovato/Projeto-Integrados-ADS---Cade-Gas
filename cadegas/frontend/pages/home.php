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
    <title>Produtos - CadêGás</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/api.js"></script>
</head>
<body>
    <div class="page-container">
        <header class="page-header">
            <h1>Produtos Disponíveis</h1>
            <div class="header-right">
                <div class="user-info">
                    <span id="userName"></span>
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

        <div id="productsList" class="products-grid"></div>

        <nav class="bottom-nav">
            <a href="home.php" class="nav-item active">
                <span>🏠</span>
                <span>Início</span>
            </a>
            <a href="cart.php" class="nav-item">
                <span>🛒</span>
                <span>Carrinho</span>
                <span id="cartBadge" class="badge"></span>
            </a>
        </nav>
    </div>

    <script>
        let produtos = [];
        let usuario = JSON.parse(localStorage.getItem('usuario') || '{}');

        document.getElementById('userName').textContent = usuario.nome || '';

        async function loadProdutos() {
            // Usando endpoint GET /produtos (swagger-1)
            // Retorna todos os produtos disponíveis de todos os distribuidores
            const resultado = await API.Produtos.listarTodos();

            if (resultado.sucesso) {
                // swagger-1 retorna {produtos: [ProdutoComDistribuidor]}
                produtos = resultado.produtos || [];

                // Adicionar campos derivados para compatibilidade
                produtos = produtos.map(produto => ({
                    ...produto,
                    // Calcular category baseado no nome
                    category: calcularCategoria(produto),
                    // Calcular size baseado no nome/descricao
                    size: extrairTamanho(produto)
                }));

                renderProdutos();
            } else {
                console.error('Erro ao carregar produtos:', resultado.erro);
                document.getElementById('productsList').innerHTML = 
                    '<p class="empty-message">Erro ao carregar produtos. Tente novamente.</p>';
            }
        }

        function calcularCategoria(produto) {
            const nome = (produto.nome || '').toLowerCase();
            if (nome.includes('botijão') || nome.includes('botijao') || 
                nome.includes('gás') || nome.includes('gas') || 
                nome.includes('p13') || nome.includes('p45')) {
                return 'gas';
            }
            if (nome.includes('água') || nome.includes('agua') || 
                nome.includes('galão') || nome.includes('galao') || 
                nome.includes('bombona')) {
                return 'water';
            }
            return 'gas'; // default
        }

        function extrairTamanho(produto) {
            const texto = `${produto.nome || ''} ${produto.descricao || ''}`.toLowerCase();
            const match = texto.match(/(\d+)\s*(kg|l|litros?)/i);
            if (match) {
                return match[1] + match[2].toUpperCase().replace('LITROS', 'L').replace('LITRO', 'L');
            }
            return '';
        }

        function renderProdutos() {
            const filter = document.getElementById('filter').value;
            const container = document.getElementById('productsList');

            // ✅ FIX Issue #2: Validação explícita do campo disponivel
            const filtered = produtos.filter(prod => {
                // Garantir que produto está disponível (swagger-1: integer 0 ou 1)
                if (parseInt(prod.disponivel) !== 1) {
                    return false;
                }
                
                if (filter === 'all') return true;
                return prod.category === filter;
            });

            if (filtered.length === 0) {
                container.innerHTML = '<p class="empty-message">Nenhum produto disponível</p>';
                return;
            }

            container.innerHTML = filtered.map(produto => `
                <div class="product-card">
                    <div class="product-image">
                        ${produto.category === 'gas' ?
                            '<img src="../assets/img/botijao_wide.jpg" alt="Botijão de Gás" style="object-position: center 20%">' :
                            '<img src="../assets/img/imgAgua20L1.jpg" alt="Bombona de Água" style="object-position: center 20%">'
                        }
                    </div>
                    <div class="product-info">
                        <h3>${produto.nome}</h3>
                        <p class="supplier-name">📦 ${produto.nome_empresa}</p>
                        <p>${produto.descricao || ''}</p>
                        ${produto.size ? `<p class="product-size">${produto.size}</p>` : ''}
                        <p class="product-price">R$ ${parseFloat(produto.preco).toFixed(2)}</p>
                        <p class="delivery-fee">+ Taxa de entrega: R$ ${parseFloat(produto.taxa_entrega).toFixed(2)}</p>
                        <button class="btn btn-primary btn-full" 
                                onclick='addToCart(${JSON.stringify(produto)})'>
                            Adicionar ao Carrinho
                        </button>
                    </div>
                </div>
            `).join('');
        }

        // ✅ FIX Issue #1: Validação de múltiplos distribuidores no carrinho
        function addToCart(produto) {
            let cart = JSON.parse(localStorage.getItem('cart') || '[]');
            
            // Verificar se carrinho já tem produtos de outro distribuidor
            if (cart.length > 0 && cart[0].id_distribuidor !== produto.id_distribuidor) {
                const nomeAtual = cart[0].nome_empresa;
                const nomeNovo = produto.nome_empresa;
                
                const confirmar = confirm(
                    `⚠️ ATENÇÃO\n\n` +
                    `Você tem produtos de "${nomeAtual}" no carrinho.\n\n` +
                    `Pedidos só podem ter produtos de um único distribuidor.\n\n` +
                    `Deseja limpar o carrinho e adicionar produto de "${nomeNovo}"?`
                );
                
                if (!confirmar) {
                    return; // Usuário cancelou
                }
                
                // Limpar carrinho para adicionar produto do novo distribuidor
                cart = [];
            }
            
            // Salvar distribuidor selecionado para o checkout
            localStorage.setItem('selected_distribuidor', produto.id_distribuidor);

            const existingItem = cart.find(item => item.id_produto === produto.id_produto);

            if (existingItem) {
                existingItem.quantidade++;
            } else {
                cart.push({
                    id_produto: produto.id_produto,
                    id_distribuidor: produto.id_distribuidor,
                    nome: produto.nome,
                    preco: parseFloat(produto.preco),
                    quantidade: 1,
                    nome_empresa: produto.nome_empresa
                });
            }

            localStorage.setItem('cart', JSON.stringify(cart));
            updateCartBadge();
            alert('✅ Produto adicionado ao carrinho!');
        }

        document.getElementById('filter').addEventListener('change', renderProdutos);

        document.getElementById('logoutBtn').addEventListener('click', async () => {
            // Destrói a $_SESSION do PHP (auth_logout.php) antes de limpar o estado client-side
            await fetch('auth_logout.php', { method: 'POST' });
            localStorage.clear();
            window.location.href = 'welcome.php';
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

        loadProdutos();
        updateCartBadge();
    </script>
</body>
</html>
