# Frontend (MVP)

SPA-free, multi-página PHP que consome a API REST do backend (`cadegas/backend/`). Sem build, sem framework, sem dependências JS além do que está em `assets/`.

## Entry point

A porta de entrada oficial é **`pages/welcome.php`**. A raiz do frontend tem apenas um redirect:

```php
<!-- cadegas/frontend/index.php -->
<?php header('Location: pages/welcome.php'); exit;
```

Logo, abrir `http://localhost/cadegas/frontend/` no navegador leva direto ao `welcome`.

## Mapa de páginas e gatekeepers

Cada `.php` em `pages/` começa com `session_start()` e um gate que redireciona se a condição não bater.

| Arquivo | Tipo | Regra do porteiro | Redireciona para |
|---|---|---|---|
| `welcome.php` | público | se `$_SESSION['usuario_id']` setado | `home.php` |
| `login.php` | público | se `$_SESSION['usuario_id']` setado | `home.php` |
| `register.php` | público | se `$_SESSION['usuario_id']` setado | `home.php` |
| `home.php` | protegido | se `$_SESSION['usuario_id']` **não** setado | `welcome.php` |
| `products.php` | protegido | idem | `welcome.php` |
| `cart.php` | protegido | idem | `welcome.php` |
| `checkout.php` | protegido | idem | `welcome.php` |
| `auth_set.php` | helper | nenhum gate, recebe POST JSON e seta a sessão | — |
| `auth_logout.php` | helper | nenhum gate, destrói a sessão | — |

## Sessão PHP

`$_SESSION['usuario_id']` é **a única coisa que controla quem passa pelas páginas**. Ela é criada e destruída por dois pequenos PHPs:

- **`pages/auth_set.php`** (`POST` JSON `{id_usuario}`) — grava `$_SESSION['usuario_id'] = id` e retorna `204`. Chamado pelo JS do `login.php` após o `POST /login` da API.
- **`pages/auth_logout.php`** (`POST`) — limpa `$_SESSION`, invalida o cookie e chama `session_destroy()`. Chamado pelo JS do `home.php` no clique do botão "Sair".

Por que essa indireção? O `POST /login` do backend é puramente uma checagem de credenciais — ele devolve `id_usuario` mas **não** toca em sessão (a API não tem estado). Quem cria a sessão é o `auth_set.php`, que roda no mesmo domínio que serve as páginas e tem acesso ao cookie `PHPSESSID`. Sem esse intermediário, o `home.php` veria sessão vazia e jogaria o usuário de volta para `welcome` em loop.

## `localStorage`

Estado client-side usado para perfil exibido na UI e para o carrinho:

| Chave | Conteúdo | Escrita | Leitura |
|---|---|---|---|
| `usuario` | JSON `{id_usuario, nome, email, telefone, endereco, cidade, estado, cep}` | `login.php` (após `POST /login`), `checkout.php` (após `GET /usuarios/{id}`) | `home.php` (mostra nome no header), `checkout.php` (informações de contato + endereço) |
| `cart` | JSON `[{id_produto, id_distribuidor, nome, preco, quantidade, nome_empresa}]` | `home.php` (`addToCart`), `cart.php` (alterar quantidade) | `cart.php`, `checkout.php` |
| `selected_distribuidor` | string com `id_distribuidor` do primeiro item | `home.php` (`addToCart`) | `checkout.php` (monta payload do pedido) |

Limpeza: `logout` chama `localStorage.clear()` (em `home.php`); `checkout.php` remove `cart` e `selected_distribuidor` após pedido confirmado.

## Cliente da API (`assets/js/api.js`)

Helper único `apiRequest(endpoint, options)` que envolve `fetch` e normaliza erros — sempre devolve `{sucesso: bool, ...}` (ou `{sucesso: false, erro: "..."}`).

Exposto em `window.API` com cinco namespaces:

| Namespace | Métodos | Endpoint |
|---|---|---|
| `API.Auth` | `registrar(userData)`, `login(email, senha)` | `POST /register`, `POST /login` |
| `API.Usuarios` | `buscar(id)` | `GET /usuarios/{id}` |
| `API.Distribuidores` | `listar()`, `listarProdutos(id)` | `GET /distribuidores`, `GET /distribuidores/{id}/produtos` |
| `API.Produtos` | `listarTodos()` | `GET /produtos` |
| `API.Pedidos` | `criar(pedidoData)`, `buscar(id)` | `POST /pedidos`, `GET /pedidos/{id}` |

Base URL é `'/cadegas/backend/public'` — definida em `API_BASE` no topo de `api.js`. Se mudar o prefixo (`ROUTES_BASE` no backend), atualize aqui também.

## Assets

- **`assets/css/style.css`** — único arquivo de estilo. Classes principais: `.section`, `.form-control`, `.form-group`, `.btn-primary`, `.btn-full`, `.user-data`, `.order-items`, `.order-item`, `.order-total`, `.info-text`, `.error-message`, `.checkout-container`, `.page-container`, `.auth-container`, `.bottom-nav`.
- **`assets/js/api.js`** — único módulo JS.
- **`assets/img/`** — imagens de produto (`botijao_wide.jpg` para gás, `imgAgua20L1.jpg` para água), escolhidas dinamicamente em `home.php`/`products.php` pelo nome do produto.

## Como estender

**Adicionar uma página nova:**

```php
<?php
session_start();

// gate (escolha um dos dois)
if (!isset($_SESSION['usuario_id'])) {       // página protegida
    header('Location: welcome.php');
    exit;
}
// ou:
// if (isset($_SESSION['usuario_id'])) {     // página pública (só deslogado)
//     header('Location: home.php');
//     exit;
// }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/api.js"></script>
</head>
<body>
    <!-- conteúdo -->
</body>
</html>
```

**Adicionar um método novo ao cliente da API:**

1. Em `assets/js/api.js`, adicione o método dentro do namespace correto (ou crie um novo objeto):
   ```js
   const Pedidos = {
       // ...existentes
       async cancelar(id) {
           return apiRequest(`/pedidos/${id}`, { method: 'DELETE' });
       }
   };
   ```
2. Se for um namespace novo, exporte-o em `window.API = { Auth, Usuarios, ..., NomeNovo }`.

## Pegadinhas

- **Autenticação é fraca.** A `$_SESSION` controla só a navegação entre páginas; a API aceita qualquer `id_usuario` no body sem provar quem é. Não use o frontend como única defesa.
- **Endereço no checkout vem do backend.** O `checkout.php` chama `GET /usuarios/{id}` no carregamento para pré-preencher o endereço, sobrescrevendo o que está no `localStorage.usuario`. Se quiser manter um endereço editado pelo usuário, precisaria persistir via `PUT /usuarios/{id}` (endpoint ainda não existe).
- **Sem framework de roteamento.** Navegação é 100% server-side via `<a href="…">` ou `window.location.href = '…'`. Não há history API, hash routing, ou SPA.
- **Imagens são escolhidas por palavra-chave no nome do produto.** Veja `calcularCategoria()` em `home.php` (linhas 86-99): se o nome contém "botijão", "gás", "P13" → gás; "água", "galão", "bombona" → água; default → gás.
- **`alert()` proibido para confirmação.** O `checkout.php` substitui a confirmação por uma seção inline `#checkoutSuccess`. Siga o padrão em páginas novas.
