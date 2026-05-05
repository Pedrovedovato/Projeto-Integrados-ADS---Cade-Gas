<!-- frontend/INTEGRATION_GUIDE.md -->

# 🔌 Guia de Integração da API no Frontend

## 📦 Setup

### 1. Incluir o cliente API

No seu `index.html`, inclua o arquivo `api-client.js`:

```html
<!DOCTYPE html>
<html>
<head>
    <title>CadêGás</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div id="app"></div>
    
    <script src="api-client.js"></script>
    <script src="main.js"></script>
</body>
</html>
```

---

## 🎯 Exemplos de Uso

### 1️⃣ Tela de Registro

```html
<form id="registerForm">
    <input type="text" id="nome" placeholder="Nome" required>
    <input type="email" id="email" placeholder="E-mail" required>
    <input type="tel" id="telefone" placeholder="Telefone" required>
    <input type="password" id="senha" placeholder="Senha" required>
    <button type="submit">Registrar</button>
</form>

<script>
document.getElementById('registerForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    try {
        const response = await api.register(
            document.getElementById('nome').value,
            document.getElementById('email').value,
            document.getElementById('telefone').value,
            document.getElementById('senha').value
        );
        
        alert('Cadastro realizado com sucesso! Faça login.');
        // Limpar formulário
        e.target.reset();
        // Redirecionar para login
        // window.location.href = '#login';
    } catch (error) {
        alert(`Erro: ${error.message}`);
    }
});
</script>
```

---

### 2️⃣ Tela de Login

```html
<form id="loginForm">
    <input type="email" id="email" placeholder="E-mail" required>
    <input type="password" id="senha" placeholder="Senha" required>
    <button type="submit">Login</button>
</form>

<script>
document.getElementById('loginForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    try {
        const response = await api.login(
            document.getElementById('email').value,
            document.getElementById('senha').value
        );
        
        alert(`Bem-vindo, ${response.nome}!`);
        // Redirecionar para tela de distribuidores
        // window.location.href = '#distribuidores';
    } catch (error) {
        alert(`Erro: ${error.message}`);
    }
});
</script>
```

---

### 3️⃣ Verificar Autenticação

```javascript
// No carregamento da página
document.addEventListener('DOMContentLoaded', () => {
    if (!api.isAuthenticated()) {
        // Redirecionar para login
        window.location.href = '#login';
    } else {
        // Carregar dados do usuário autenticado
        carregarDistribuidores();
    }
});
```

---

### 4️⃣ Listar Distribuidores

```html
<div id="distribuidoresList"></div>

<script>
async function carregarDistribuidores() {
    try {
        const distribuidores = await api.listDistribuidores();
        
        let html = '<h2>Distribuidores Disponíveis</h2>';
        html += '<ul>';
        
        distribuidores.forEach(dist => {
            html += `
                <li>
                    <h3>${dist.nome_empresa}</h3>
                    <p>Taxa de Entrega: R$ ${dist.taxa_entrega.toFixed(2)}</p>
                    <p>Telefone: ${dist.telefone}</p>
                    <button onclick="abrirDistribuidor(${dist.id_distribuidor})">
                        Ver Produtos
                    </button>
                </li>
            `;
        });
        
        html += '</ul>';
        document.getElementById('distribuidoresList').innerHTML = html;
    } catch (error) {
        console.error('Erro ao carregar distribuidores:', error);
    }
}
</script>
```

---

### 5️⃣ Listar Produtos de um Distribuidor

```html
<div id="produtosList"></div>

<script>
async function abrirDistribuidor(distribuidorId) {
    try {
        const response = await api.listProdutos(distribuidorId);
        
        let html = `<h2>Produtos - Distribuidor ${response.distribuidor_id}</h2>`;
        html += '<ul>';
        
        response.produtos.forEach(produto => {
            html += `
                <li>
                    <h4>${produto.tipo}</h4>
                    <p>${produto.descricao}</p>
                    <p>Preço: R$ ${produto.preco.toFixed(2)}</p>
                    <p>Estoque: ${produto.estoque} unidades</p>
                    <input type="number" id="qty-${produto.id_produto}" 
                           min="0" max="${produto.estoque}" value="0">
                </li>
            `;
        });
        
        html += '</ul>';
        html += `<button onclick="finalizarPedido(${response.distribuidor_id})">
                    Finalizar Pedido
                </button>`;
        
        document.getElementById('produtosList').innerHTML = html;
    } catch (error) {
        console.error('Erro ao carregar produtos:', error);
    }
}
</script>
```

---

### 6️⃣ Criar Pedido

```javascript
async function finalizarPedido(distribuidorId) {
    try {
        // Coletar itens do pedido
        const itens = [];
        document.querySelectorAll('input[id^="qty-"]').forEach(input => {
            const quantidade = parseInt(input.value);
            if (quantidade > 0) {
                const produtoId = parseInt(input.id.replace('qty-', ''));
                itens.push({
                    id_produto: produtoId,
                    quantidade: quantidade
                });
            }
        });
        
        if (itens.length === 0) {
            alert('Selecione pelo menos um produto');
            return;
        }
        
        // Criar pedido
        const pedido = await api.criarPedido(distribuidorId, itens);
        
        alert(`Pedido criado com sucesso!\nID: ${pedido.id_pedido}\nTotal: R$ ${pedido.total.toFixed(2)}`);
        
        // Buscar e mostrar detalhes
        await mostrarDetalhePedido(pedido.id_pedido);
    } catch (error) {
        alert(`Erro ao criar pedido: ${error.message}`);
    }
}
```

---

### 7️⃣ Ver Detalhes do Pedido

```html
<div id="pedidoDetalhes"></div>

<script>
async function mostrarDetalhePedido(pedidoId) {
    try {
        const response = await api.buscarPedido(pedidoId);
        
        let html = `<h2>Detalhes do Pedido #${response.pedido.id_pedido}</h2>`;
        html += `<p>Total: R$ ${response.pedido.total.toFixed(2)}</p>`;
        html += `<p>Criado em: ${response.pedido.criado_em}</p>`;
        html += '<h3>Itens:</h3>';
        html += '<ul>';
        
        response.itens.forEach(item => {
            html += `
                <li>
                    <p>Produto #${item.id_produto}</p>
                    <p>Quantidade: ${item.quantidade}</p>
                    <p>Preço Unitário: R$ ${item.preco_unitario.toFixed(2)}</p>
                    <p>Subtotal: R$ ${item.subtotal.toFixed(2)}</p>
                </li>
            `;
        });
        
        html += '</ul>';
        html += `<p><strong>${response.mensagem}</strong></p>`;
        
        document.getElementById('pedidoDetalhes').innerHTML = html;
    } catch (error) {
        console.error('Erro ao buscar pedido:', error);
    }
}
</script>
```

---

## 🔧 Tratamento de Erros

```javascript
// O cliente API lança erros com status e mensagens específicas
async function exemploComTratamento() {
    try {
        const result = await api.login(email, senha);
        // Sucesso
    } catch (error) {
        if (error.status === 401) {
            // Erro de autenticação
            console.log('Credenciais incorretas');
        } else if (error.status === 409) {
            // Conflito (email duplicado, etc)
            console.log('Recurso já existe');
        } else if (error.status === 404) {
            // Não encontrado
            console.log('Recurso não existe');
        } else if (error.status === 400) {
            // Bad request
            console.log('Dados inválidos');
        } else if (error.status === 500) {
            // Erro no servidor
            console.log('Erro no servidor');
        }
    }
}
```

---

## 💾 Gerenciar Autenticação

```javascript
// Salvar ID do usuário (automático após login)
api.saveUserId(123);

// Recuperar ID do usuário
const userId = api.getUserId();

// Verificar se está autenticado
if (api.isAuthenticated()) {
    console.log('Usuário autenticado');
}

// Fazer logout
api.logout();
```

---

## 🌐 Variáveis de Ambiente

No seu `package.json` ou no build process, configure a variável `NODE_ENV`:

```bash
# Desenvolvimento
NODE_ENV=development npm start

# Staging/Homologação
NODE_ENV=staging npm build

# Produção
NODE_ENV=production npm build
```

---

## 📱 Exemplo Completo (SPA simples)

```html
<!DOCTYPE html>
<html>
<head>
    <title>CadêGás</title>
</head>
<body>
    <div id="app"></div>
    
    <script src="api-client.js"></script>
    <script>
        // Roteamento simples
        const routes = {
            '#login': mostrarLogin,
            '#register': mostrarRegistro,
            '#distribuidores': mostrarDistribuidores,
            '#': mostrarHome
        };

        function mostrarHome() {
            if (api.isAuthenticated()) {
                mostrarDistribuidores();
            } else {
                mostrarLogin();
            }
        }

        function mostrarLogin() {
            document.getElementById('app').innerHTML = `
                <h1>Login</h1>
                <form id="loginForm">
                    <input type="email" id="email" placeholder="E-mail" required>
                    <input type="password" id="senha" placeholder="Senha" required>
                    <button type="submit">Login</button>
                    <a href="#register">Registrar</a>
                </form>
            `;

            document.getElementById('loginForm').addEventListener('submit', async (e) => {
                e.preventDefault();
                try {
                    await api.login(
                        document.getElementById('email').value,
                        document.getElementById('senha').value
                    );
                    window.location.hash = '#distribuidores';
                } catch (error) {
                    alert(`Erro: ${error.message}`);
                }
            });
        }

        function mostrarRegistro() {
            // ... implementar
        }

        async function mostrarDistribuidores() {
            const distribuidores = await api.listDistribuidores();
            // ... renderizar
        }

        // Listener para mudanças de hash
        window.addEventListener('hashchange', () => {
            const route = routes[window.location.hash] || routes['#'];
            route();
        });

        // Carregar página inicial
        mostrarHome();
    </script>
</body>
</html>
```

---

## 🚀 Checklist de Implementação

- [ ] Incluir `api-client.js` no HTML
- [ ] Implementar tela de registro
- [ ] Implementar tela de login
- [ ] Verificar autenticação ao carregar página
- [ ] Listar distribuidores
- [ ] Listar produtos por distribuidor
- [ ] Criar pedido
- [ ] Ver detalhes do pedido
- [ ] Implementar logout
- [ ] Tratar erros de API
- [ ] Testar em desenvolvimento
- [ ] Configurar para produção

---

**Próximas melhorias:** Adicionar tokens JWT, cache de dados, WebSockets para notificações em tempo real.
