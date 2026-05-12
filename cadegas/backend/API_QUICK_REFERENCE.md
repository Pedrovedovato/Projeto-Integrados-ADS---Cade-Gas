# 🚀 API Reference Rápida — CadêGás

**Base URL (dev):** `http://localhost/cadegas/backend/public`
*(controlado por `ROUTES_BASE` no `.env`)*

---

## 🔐 AUTENTICAÇÃO

### Registrar
```
POST /register
Content-Type: application/json

{
  "nome": "João Silva",
  "email": "joao@example.com",
  "telefone": "(11) 98765-4321",
  "senha": "senha123",
  "endereco": "Av. Paulista, 1000",   // opcional
  "cidade": "São Paulo",               // opcional
  "estado": "SP",                      // opcional
  "cep": "01310-100"                   // opcional
}

✅ 201: { "mensagem": "...", "id_usuario": 1, "email": "..." }
❌ 400: { "erro": "Dados obrigatórios não preenchidos" | "E-mail inválido" | "Senha deve ter no mínimo 6 caracteres" }
❌ 409: { "erro": "E-mail já cadastrado" }
```

### Login
```
POST /login
Content-Type: application/json

{
  "email": "joao@example.com",
  "senha": "senha123"
}

✅ 200: { "mensagem": "...", "id_usuario": 1, "nome": "...", "email": "...", "usuario": {...} }
❌ 401: { "erro": "E-mail ou senha inválidos" }
```

---

## 🏪 DISTRIBUIDORES

### Listar Ativos
```
GET /distribuidores

✅ 200: [
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
  },
  ...
]
```

### Listar Produtos Disponíveis (TODOS os distribuidores) — usado na tela inicial
```
GET /produtos

✅ 200: {
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
    },
    ...
  ]
}
```

### Listar Produtos de um Distribuidor (filtro alternativo)
```
GET /distribuidores/{id}/produtos

✅ 200: {
  "distribuidor_id": 1,
  "produtos": [
    { "id_produto": 1, "id_distribuidor": 1, "nome": "Botijão P13 (13 kg)",
      "descricao": "Botijão residencial padrão", "preco": 95.00, "disponivel": 1 },
    ...
  ]
}
```

---

## 🛒 PEDIDOS

### Criar Pedido
```
POST /pedidos
Content-Type: application/json

{
  "id_usuario": 1,
  "id_distribuidor": 1,
  "itens": [
    { "id_produto": 1, "quantidade": 2 },
    { "id_produto": 3, "quantidade": 1 }
  ],
  "forma_pagamento": "pix",            // opcional, default "dinheiro"
  "endereco_entrega": "Av. Principal" // opcional, default = endereço do usuário
}

✅ 201: {
  "mensagem": "Pedido criado com sucesso",
  "id_pedido": 5,
  "subtotal": 215.00,
  "taxa_entrega": 8.00,
  "total": 223.00,
  "forma_pagamento": "pix",
  "status": "pendente"
}
❌ 400: payload/item inválido, produto fora do distribuidor, forma de pagamento inválida
❌ 401: id_usuario inexistente
❌ 404: distribuidor ou produto não encontrado
❌ 409: distribuidor inativo / produto indisponível
❌ 500: falha na transação (rollback aplicado)
```

### Buscar Pedido
```
GET /pedidos/{id}

✅ 200: {
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
    { "id_produto": 1, "nome": "Botijão P13 (13 kg)", "descricao": "...",
      "quantidade": 2, "preco_unitario": 95.00, "subtotal": 190.00 },
    ...
  ],
  "mensagem": "O distribuidor entrará em contato para confirmar a entrega"
}
❌ 404: { "erro": "Pedido não encontrado" }
```

---

## 💡 Snippets Úteis

### Salvar / Recuperar ID do Usuário
```javascript
localStorage.setItem('id_usuario', data.id_usuario);
const idUsuario = parseInt(localStorage.getItem('id_usuario'), 10);
```

### Estrutura de Requisição Padrão
```javascript
fetch(url, {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify(dados)
})
  .then(res => res.json())
  .then(data => console.log(data))
  .catch(err => console.error(err));
```

---

## 📊 Fluxo Completo (exemplo)

```javascript
const API = 'http://localhost/cadegas/backend/public';

// 1. Login
const loginData = await fetch(`${API}/login`, {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ email: 'maria@email.com', senha: 'senha123' })
}).then(r => r.json());

const idUsuario = loginData.id_usuario;

// 2. Distribuidores
const distribuidores = await fetch(`${API}/distribuidores`).then(r => r.json());

// 3. Produtos (distribuidor 1)
const produtos = await fetch(`${API}/distribuidores/1/produtos`).then(r => r.json());

// 4. Pedido (com forma de pagamento)
const pedido = await fetch(`${API}/pedidos`, {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    id_usuario: idUsuario,
    id_distribuidor: 1,
    itens: [{ id_produto: 1, quantidade: 2 }],
    forma_pagamento: 'pix'
  })
}).then(r => r.json());

// 5. Detalhes do pedido
const detalhes = await fetch(`${API}/pedidos/${pedido.id_pedido}`).then(r => r.json());
console.log(detalhes);
```

---

## 🔄 Estados HTTP

- **2xx** — sucesso
  - `200 OK`, `201 Created`
- **4xx** — erro do cliente
  - `400` payload inválido
  - `401` não autenticado / credenciais inválidas
  - `404` recurso não existe
  - `409` conflito (e-mail duplicado, distribuidor inativo, produto indisponível)
- **5xx** — erro do servidor
  - `500` falha interna (com transação revertida em `POST /pedidos`)

Erros sempre no formato `{"erro": "mensagem"}`.

---

**Mais detalhes:** veja `API_DOCUMENTATION.md`.
