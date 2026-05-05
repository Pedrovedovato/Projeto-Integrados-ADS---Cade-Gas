# 🚀 API Reference Rápida - CadêGás

**Base URL:** `http://localhost/backend/public`

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
  "senha": "senha123"
}

✅ 201: { "mensagem": "...", "id_usuario": 1, "email": "..." }
❌ 400: { "erro": "Dados obrigatórios não preenchidos" }
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

✅ 200: { "mensagem": "...", "id_usuario": 1, "nome": "...", "email": "..." }
❌ 401: { "erro": "E-mail ou senha incorretos" }
```

---

## 🏪 DISTRIBUIDORES

### Listar Todos
```
GET /distribuidores

✅ 200: [ { "id_distribuidor": 1, "nome_empresa": "...", "taxa_entrega": 10, ... }, ... ]
```

### Listar Produtos
```
GET /distribuidores/{id}/produtos

✅ 200: {
  "distribuidor_id": 1,
  "produtos": [
    { "id_produto": 1, "tipo": "Botijão P13", "preco": 89.90, "estoque": 50 },
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
    { "id_produto": 2, "quantidade": 1 }
  ]
}

✅ 201: { "mensagem": "Pedido criado com sucesso", "id_pedido": 5, "total": 189.80 }
❌ 400: { "erro": "Dados obrigatórios não preenchidos" }
```

### Buscar Pedido
```
GET /pedidos/{id}

✅ 200: {
  "pedido": {
    "id_pedido": 5,
    "id_usuario": 1,
    "id_distribuidor": 1,
    "total": 189.80,
    "criado_em": "2024-05-05 10:30:00"
  },
  "itens": [
    { "id_item": 1, "id_produto": 1, "quantidade": 2, "preco_unitario": 89.90, "subtotal": 179.80 },
    ...
  ],
  "mensagem": "O distribuidor entrará em contato para confirmar a entrega"
}

❌ 404: { "erro": "Pedido não encontrado" }
```

---

## 💡 Snippets Úteis

### Salvar ID do Usuário (após login)
```javascript
localStorage.setItem('id_usuario', data.id_usuario);
```

### Recuperar ID do Usuário
```javascript
const idUsuario = localStorage.getItem('id_usuario');
```

### Estrutura de Requisição Padrão
```javascript
fetch(url, {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify(dados)
})
  .then(res => res.json())
  .then(data => console.log(data))
  .catch(err => console.error(err));
```

---

## 📊 Exemplo: Fluxo Completo

```javascript
// 1. Login
const loginData = await fetch('http://localhost/backend/public/login', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ email: 'joao@example.com', senha: 'senha123' })
}).then(r => r.json());

const idUsuario = loginData.id_usuario;

// 2. Listar Distribuidores
const distribuidores = await fetch('http://localhost/backend/public/distribuidores')
  .then(r => r.json());

// 3. Listar Produtos (distribuidor 1)
const produtos = await fetch('http://localhost/backend/public/distribuidores/1/produtos')
  .then(r => r.json());

// 4. Criar Pedido
const pedido = await fetch('http://localhost/backend/public/pedidos', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    id_usuario: idUsuario,
    id_distribuidor: 1,
    itens: [
      { id_produto: 1, quantidade: 2 },
      { id_produto: 2, quantidade: 1 }
    ]
  })
}).then(r => r.json());

// 5. Buscar Detalhes do Pedido
const detalhesPedido = await fetch(`http://localhost/backend/public/pedidos/${pedido.id_pedido}`)
  .then(r => r.json());

console.log(detalhesPedido);
```

---

## 🔄 Estados HTTP

- **2xx (Success)**: Requisição bem-sucedida
  - `200 OK`: Dado retornado
  - `201 Created`: Recurso criado
  
- **4xx (Client Error)**: Erro na requisição
  - `400 Bad Request`: Dados inválidos
  - `401 Unauthorized`: Autenticação falhou
  - `404 Not Found`: Recurso não existe
  - `409 Conflict`: Conflito (ex: email duplicado)
  
- **5xx (Server Error)**: Erro no servidor
  - `500 Internal Server Error`: Erro interno

---

**Dúvidas?** Consulte `API_DOCUMENTATION.md` para documentação completa.
