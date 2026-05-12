# 📚 Documentação da API CadêGás

## Visão Geral

A API CadêGás é uma REST API desenvolvida em PHP que fornece endpoints para comunicação entre o frontend e o backend do sistema de entrega de gás e água.

**Versão:** 1.1.0
**Base URL (dev):** `http://localhost/cadegas/backend/public`

> O prefixo da URL é controlado por `ROUTES_BASE` no `.env`. Em produção, ajuste para o caminho onde o `public/` é servido.

---

## 🔑 Autenticação

A API utiliza autenticação simples por e-mail e senha. O `id_usuario` é retornado no cadastro e no login; o frontend deve guardá-lo (ex.: `localStorage`) e enviá-lo no body de endpoints autenticados (ex.: `POST /pedidos`).

> Nesta versão **MVP**, o backend só verifica que o `id_usuario` enviado existe — não há token nem sessão. Auth completa (JWT/sessão) está prevista para o pós-MVP.

**Fluxo:**
1. `POST /register` — cria conta
2. `POST /login` — devolve `id_usuario`
3. Frontend armazena `id_usuario`
4. Endpoints autenticados (ex.: `POST /pedidos`) recebem `id_usuario` no body

---

## 📋 Endpoints

### 1️⃣ AUTENTICAÇÃO

#### POST /register
Cria uma nova conta de usuário.

**Request Body:**
```json
{
  "nome": "João Silva",
  "email": "joao@example.com",
  "telefone": "(11) 98765-4321",
  "senha": "senha123",
  "endereco": "Av. Paulista, 1000",
  "cidade": "São Paulo",
  "estado": "SP",
  "cep": "01310-100"
}
```

| Campo | Obrigatório | Observação |
|-------|-------------|------------|
| nome, email, telefone, senha | Sim | senha tem mínimo 6 caracteres |
| endereco, cidade, estado, cep | Não | US03 — pode ser informado depois |

**Responses:**
- ✅ **201 Created**
  ```json
  {
    "mensagem": "Usuário cadastrado com sucesso",
    "id_usuario": 1,
    "email": "joao@example.com"
  }
  ```
- ❌ **400 Bad Request** — campos obrigatórios ausentes, e-mail inválido, ou senha < 6 caracteres
- ❌ **409 Conflict** — e-mail já cadastrado
- ❌ **500 Internal Server Error** — falha ao gravar no banco

---

#### POST /login
Autentica um usuário existente.

**Request Body:**
```json
{
  "email": "joao@example.com",
  "senha": "senha123"
}
```

**Responses:**
- ✅ **200 OK**
  ```json
  {
    "mensagem": "Login realizado com sucesso",
    "id_usuario": 1,
    "nome": "João Silva",
    "email": "joao@example.com",
    "usuario": { "id": 1, "nome": "João Silva", "email": "joao@example.com" }
  }
  ```
  > O bloco `usuario` é mantido por compatibilidade com clientes antigos.
- ❌ **400 Bad Request** — e-mail ou senha não informados
- ❌ **401 Unauthorized** — e-mail ou senha inválidos *(mensagem genérica para não revelar se o e-mail existe)*

---

### 2️⃣ USUÁRIOS

#### GET /usuarios/{id}
Retorna o perfil completo do usuário. Usado pelo frontend no checkout para pré-preencher o endereço de entrega e exibir as informações de contato.

**Path params:**

| Param | Tipo | Observação |
|-------|------|------------|
| id | int positivo | id_usuario retornado por `/login` ou `/register` |

**Responses:**
- ✅ **200 OK**
  ```json
  {
    "id_usuario": 1,
    "nome": "João Silva",
    "email": "joao@example.com",
    "telefone": "(11) 98765-4321",
    "endereco": "Av. Paulista, 1000",
    "cidade": "São Paulo",
    "estado": "SP",
    "cep": "01310-100"
  }
  ```
  > Campos nulos no banco vêm como string vazia (`""`). `id_usuario` é sempre inteiro.
- ❌ **400 Bad Request** — `{"erro": "ID de usuário inválido"}` (id <= 0 ou não numérico)
- ❌ **404 Not Found** — `{"erro": "Usuário não encontrado"}`

---

### 3️⃣ DISTRIBUIDORES

#### GET /distribuidores
Lista os distribuidores ativos.

**Responses:**
- ✅ **200 OK**
  ```json
  [
    {
      "id_distribuidor": 1,
      "nome_empresa": "GásFácil Distribuidora",
      "cnpj": "12.345.678/0001-90",
      "telefone": "(13) 99000-0001",
      "endereco": "Rua das Palmeiras, 100",
      "cidade": "Bertioga",
      "estado": "SP",
      "taxa_entrega": 8.00,
      "ativo": 1
    }
  ]
  ```

---

#### GET /produtos
Lista **todos os produtos disponíveis** de qualquer distribuidor ativo. Inclui `nome_empresa` e `taxa_entrega` do distribuidor (do JOIN) — é o endpoint da tela inicial pós-login.

**Responses:**
- ✅ **200 OK**
  ```json
  {
    "produtos": [
      {
        "id_produto": 1,
        "id_distribuidor": 1,
        "nome": "Botijão P13 (13 kg)",
        "descricao": "Botijão residencial padrão",
        "preco": 95.00,
        "disponivel": 1,
        "nome_empresa": "GásFácil Distribuidora",
        "taxa_entrega": 8.00
      }
    ]
  }
  ```

> Filtros aplicados pelo backend: `produto.disponivel = 1` E `distribuidor.ativo = 1`. Ordem: `nome` do produto, depois `nome_empresa`.

---

#### GET /distribuidores/{id}/produtos
Lista os produtos **disponíveis** (`disponivel = 1`) de um distribuidor específico (filtro alternativo).

**Responses:**
- ✅ **200 OK**
  ```json
  {
    "distribuidor_id": 1,
    "produtos": [
      {
        "id_produto": 1,
        "id_distribuidor": 1,
        "nome": "Botijão P13 (13 kg)",
        "descricao": "Botijão residencial padrão",
        "preco": 95.00,
        "disponivel": 1
      }
    ]
  }
  ```

---

### 4️⃣ PEDIDOS

#### POST /pedidos
Cria um novo pedido. O backend calcula `subtotal`, `taxa_entrega` (snapshot do distribuidor) e `total`.

**Request Body:**
```json
{
  "id_usuario": 1,
  "id_distribuidor": 1,
  "itens": [
    { "id_produto": 1, "quantidade": 2 },
    { "id_produto": 3, "quantidade": 1 }
  ],
  "forma_pagamento": "pix",
  "endereco_entrega": "Av. Principal, 50"
}
```

| Campo | Obrigatório | Observação |
|-------|-------------|------------|
| id_usuario | Sim | precisa existir no banco (auth fraca do MVP) |
| id_distribuidor | Sim | precisa estar ativo |
| itens | Sim | array não vazio; cada item com `id_produto` e `quantidade > 0` |
| forma_pagamento | Não | um de `dinheiro`, `pix`, `cartao` (default `dinheiro`) |
| endereco_entrega | Não | se ausente, usa o endereço cadastrado do usuário |

**Responses:**
- ✅ **201 Created**
  ```json
  {
    "mensagem": "Pedido criado com sucesso",
    "id_pedido": 5,
    "subtotal": 215.00,
    "taxa_entrega": 8.00,
    "total": 223.00,
    "forma_pagamento": "pix",
    "status": "pendente"
  }
  ```
- ❌ **400 Bad Request** — payload inválido, item inválido, produto não pertence ao distribuidor informado, ou forma de pagamento inválida
- ❌ **401 Unauthorized** — `id_usuario` não existe
- ❌ **404 Not Found** — distribuidor ou produto não existe
- ❌ **409 Conflict** — distribuidor inativo ou produto indisponível
- ❌ **500 Internal Server Error** — falha na transação (pedido + itens fazem rollback)

---

#### GET /pedidos/{id}
Recupera os detalhes de um pedido.

**Responses:**
- ✅ **200 OK**
  ```json
  {
    "pedido": {
      "id_pedido": 5,
      "id_usuario": 1,
      "id_distribuidor": 1,
      "status": "pendente",
      "subtotal": 215.00,
      "taxa_entrega": 8.00,
      "total": 223.00,
      "forma_pagamento": "pix",
      "endereco_entrega": "Av. Principal, 50",
      "criado_em": "2026-05-06 10:30:00"
    },
    "itens": [
      {
        "id_produto": 1,
        "nome": "Botijão P13 (13 kg)",
        "descricao": "Botijão residencial padrão",
        "quantidade": 2,
        "preco_unitario": 95.00,
        "subtotal": 190.00
      }
    ],
    "mensagem": "O distribuidor entrará em contato para confirmar a entrega"
  }
  ```
- ❌ **404 Not Found** — pedido não encontrado

---

## 🛠️ Como Usar a API no Frontend

### Exemplo em JavaScript/Fetch API

#### 1. Registrar Usuário
```javascript
async function registrar(dados) {
  const response = await fetch('http://localhost/cadegas/backend/public/register', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(dados) // { nome, email, telefone, senha, endereco?, cidade?, estado?, cep? }
  });
  return response.json();
}
```

#### 2. Fazer Login
```javascript
async function login(email, senha) {
  const response = await fetch('http://localhost/cadegas/backend/public/login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, senha })
  });
  const data = await response.json();
  if (response.ok) localStorage.setItem('id_usuario', data.id_usuario);
  return data;
}
```

#### 3. Listar Distribuidores
```javascript
async function listarDistribuidores() {
  const response = await fetch('http://localhost/cadegas/backend/public/distribuidores');
  return response.json();
}
```

#### 4. Listar Produtos de um Distribuidor
```javascript
async function listarProdutos(distribuidorId) {
  const response = await fetch(
    `http://localhost/cadegas/backend/public/distribuidores/${distribuidorId}/produtos`
  );
  return response.json();
}
```

#### 5. Criar Pedido
```javascript
async function criarPedido(idDistribuidor, itens, formaPagamento = 'dinheiro') {
  const idUsuario = parseInt(localStorage.getItem('id_usuario'), 10);

  const response = await fetch('http://localhost/cadegas/backend/public/pedidos', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      id_usuario: idUsuario,
      id_distribuidor: idDistribuidor,
      itens, // [{ id_produto, quantidade }, ...]
      forma_pagamento: formaPagamento // 'dinheiro' | 'pix' | 'cartao'
    })
  });
  return response.json();
}
```

#### 6. Buscar Detalhes do Pedido
```javascript
async function buscarPedido(pedidoId) {
  const response = await fetch(
    `http://localhost/cadegas/backend/public/pedidos/${pedidoId}`
  );
  return response.json();
}
```

---

## 📊 Fluxo Típico do App

```
1. Usuário abre o app
   ↓
2. Verifica se está autenticado (localStorage.id_usuario)
   ├─ Não: tela de login/registro
   └─ Sim: continua
   ↓
3. GET /distribuidores  →  lista os distribuidores ativos
   ↓
4. Usuário escolhe distribuidor
   ↓
5. GET /distribuidores/{id}/produtos  →  produtos disponíveis
   ↓
6. Usuário monta o carrinho e escolhe forma de pagamento
   ↓
7. POST /pedidos  →  cria o pedido (subtotal + taxa = total)
   ↓
8. Tela de confirmação com id_pedido, subtotal, taxa, total
   ↓
9. (Opcional) GET /pedidos/{id}  →  consulta o pedido
```

---

## 📝 Estrutura de Dados (resumo)

### Usuário
```json
{
  "id_usuario": 1,
  "nome": "João Silva",
  "email": "joao@example.com",
  "telefone": "(11) 98765-4321",
  "endereco": "Av. Paulista, 1000",
  "cidade": "São Paulo",
  "estado": "SP",
  "cep": "01310-100",
  "ativo": true
}
```

### Distribuidor (linha completa)
```json
{
  "id_distribuidor": 1,
  "nome_empresa": "GásFácil Distribuidora",
  "taxa_entrega": 8.00,
  "ativo": 1
}
```

### Produto
```json
{
  "id_produto": 1,
  "id_distribuidor": 1,
  "nome": "Botijão P13 (13 kg)",
  "descricao": "Botijão residencial padrão",
  "preco": 95.00,
  "disponivel": 1
}
```

### Pedido (linha completa)
```json
{
  "id_pedido": 1,
  "id_usuario": 1,
  "id_distribuidor": 1,
  "status": "pendente",
  "subtotal": 190.00,
  "taxa_entrega": 8.00,
  "total": 198.00,
  "forma_pagamento": "pix",
  "endereco_entrega": "Av. Principal, 50"
}
```

---

## 🐛 Códigos de Erro

| Código | Significado | Quando aparece |
|--------|-------------|----------------|
| **200** | OK | Requisição bem-sucedida |
| **201** | Created | Recurso criado com sucesso |
| **400** | Bad Request | Dados inválidos, item inválido, e-mail mal formado, forma de pagamento inválida |
| **401** | Unauthorized | Credenciais inválidas no login; `id_usuario` inexistente em `POST /pedidos` |
| **404** | Not Found | Pedido / distribuidor / produto inexistente |
| **409** | Conflict | E-mail já cadastrado, distribuidor inativo, produto indisponível |
| **500** | Server Error | Falha de banco / transação |

Resposta de erro tem sempre o formato `{"erro": "mensagem"}`.

---

## 📖 Visualizar API Interativamente

- **Swagger UI:** cole `swagger.json` em [editor.swagger.io](https://editor.swagger.io/)
- **Postman / Insomnia:** importe `swagger.json`

---

## 🚀 Próximos Passos (fora do escopo P0)

- [ ] Autenticação com tokens (JWT/sessão)
- [ ] Endpoints `GET /usuarios/{id}/pedidos`, `PATCH /pedidos/{id}/status`, `DELETE /pedidos/{id}`
- [ ] Filtrar distribuidores por proximidade (`latitude`/`longitude` já no schema)
- [ ] Rastreamento de pedidos em tempo real
- [ ] Sistema de avaliação de distribuidores
- [ ] Integração com sistema de pagamento online

---

**Última atualização:** 06/05/2026
