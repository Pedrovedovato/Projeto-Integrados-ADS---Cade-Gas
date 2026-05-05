# 📚 Documentação da API CadêGás

## Visão Geral

A API CadêGás é uma REST API desenvolvida em PHP que fornece endpoints para comunicação entre o frontend e o backend do sistema de entrega de gás e água.

**Versão:** 1.0.0  
**Base URL:** `http://localhost/backend/public` (desenvolvimento)

---

## 🔑 Autenticação

Atualmente, a API utiliza autenticação simples com email e senha. O ID do usuário é retornado no login e deve ser armazenado no frontend para requisições subsequentes.

**Fluxo de Autenticação:**
1. Usuário faz registro em `/register`
2. Usuário faz login em `/login`
3. Frontend armazena o `id_usuario` retornado
4. Este ID é incluído em futuras requisições (ex: criar pedidos)

---

## 📋 Endpoints

### 1️⃣ AUTENTICAÇÃO

#### POST /register
Cria uma nova conta de usuário no sistema.

**Request Body:**
```json
{
  "nome": "João Silva",
  "email": "joao@example.com",
  "telefone": "(11) 98765-4321",
  "senha": "senha123"
}
```

**Responses:**
- ✅ **201 Created**
  ```json
  {
    "mensagem": "Usuário criado com sucesso",
    "id_usuario": 1,
    "email": "joao@example.com"
  }
  ```
- ❌ **400 Bad Request** - Dados obrigatórios não preenchidos
- ❌ **409 Conflict** - E-mail já cadastrado

---

#### POST /login
Autentica um usuário e retorna seus dados.

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
    "email": "joao@example.com"
  }
  ```
- ❌ **400 Bad Request** - E-mail ou senha não informados
- ❌ **401 Unauthorized** - E-mail ou senha incorretos

---

### 2️⃣ DISTRIBUIDORES

#### GET /distribuidores
Lista todos os distribuidores ativos e disponíveis para pedido.

**Responses:**
- ✅ **200 OK**
  ```json
  [
    {
      "id_distribuidor": 1,
      "nome_empresa": "Gás Brasil Distribuições",
      "cnpj": "12.345.678/0001-99",
      "responsavel": "João Manager",
      "email": "contato@gasbrasil.com",
      "telefone": "(11) 3456-7890",
      "endereco": "Rua A, 123",
      "cidade": "São Paulo",
      "estado": "SP",
      "cep": "01310-100",
      "latitude": -23.561684,
      "longitude": -46.656139,
      "taxa_entrega": 10.00,
      "ativo": true
    },
    ...
  ]
  ```

---

#### GET /distribuidores/{id}/produtos
Lista todos os produtos de um distribuidor específico.

**Path Parameters:**
- `id` (integer) - ID do distribuidor

**Responses:**
- ✅ **200 OK**
  ```json
  {
    "distribuidor_id": 1,
    "produtos": [
      {
        "id_produto": 1,
        "id_distribuidor": 1,
        "tipo": "Botijão P13",
        "descricao": "Botijão de gás 13kg padrão",
        "preco": 89.90,
        "estoque": 50
      },
      {
        "id_produto": 2,
        "id_distribuidor": 1,
        "tipo": "Galão de Água",
        "descricao": "Galão de água mineral 20L",
        "preco": 15.00,
        "estoque": 100
      }
    ]
  }
  ```

---

### 3️⃣ PEDIDOS

#### POST /pedidos
Cria um novo pedido de produtos.

**Request Body:**
```json
{
  "id_usuario": 1,
  "id_distribuidor": 1,
  "itens": [
    {
      "id_produto": 1,
      "quantidade": 2
    },
    {
      "id_produto": 2,
      "quantidade": 1
    }
  ]
}
```

**Responses:**
- ✅ **201 Created**
  ```json
  {
    "mensagem": "Pedido criado com sucesso",
    "id_pedido": 5,
    "total": 189.80
  }
  ```
- ❌ **400 Bad Request** - Dados obrigatórios não preenchidos

---

#### GET /pedidos/{id}
Recupera os detalhes completos de um pedido.

**Path Parameters:**
- `id` (integer) - ID do pedido

**Responses:**
- ✅ **200 OK**
  ```json
  {
    "pedido": {
      "id_pedido": 5,
      "id_usuario": 1,
      "id_distribuidor": 1,
      "total": 189.80,
      "criado_em": "2024-05-05 10:30:00"
    },
    "itens": [
      {
        "id_item": 1,
        "id_produto": 1,
        "quantidade": 2,
        "preco_unitario": 89.90,
        "subtotal": 179.80
      },
      {
        "id_item": 2,
        "id_produto": 2,
        "quantidade": 1,
        "preco_unitario": 15.00,
        "subtotal": 15.00
      }
    ],
    "mensagem": "O distribuidor entrará em contato para confirmar a entrega"
  }
  ```
- ❌ **404 Not Found** - Pedido não encontrado

---

## 🛠️ Como Usar a API no Frontend

### Exemplo em JavaScript/Fetch API

#### 1. Registrar Usuário
```javascript
async function registrar(nome, email, telefone, senha) {
  const response = await fetch('http://localhost/backend/public/register', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      nome,
      email,
      telefone,
      senha
    })
  });
  
  return await response.json();
}
```

#### 2. Fazer Login
```javascript
async function login(email, senha) {
  const response = await fetch('http://localhost/backend/public/login', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      email,
      senha
    })
  });
  
  const data = await response.json();
  if (response.ok) {
    // Armazenar o ID do usuário
    localStorage.setItem('id_usuario', data.id_usuario);
  }
  return data;
}
```

#### 3. Listar Distribuidores
```javascript
async function listarDistribuidores() {
  const response = await fetch('http://localhost/backend/public/distribuidores');
  return await response.json();
}
```

#### 4. Listar Produtos de um Distribuidor
```javascript
async function listarProdutos(distribuidorId) {
  const response = await fetch(
    `http://localhost/backend/public/distribuidores/${distribuidorId}/produtos`
  );
  return await response.json();
}
```

#### 5. Criar Pedido
```javascript
async function criarPedido(idDistribuidor, itens) {
  const idUsuario = localStorage.getItem('id_usuario');
  
  const response = await fetch('http://localhost/backend/public/pedidos', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      id_usuario: parseInt(idUsuario),
      id_distribuidor: idDistribuidor,
      itens
    })
  });
  
  return await response.json();
}
```

#### 6. Buscar Detalhes do Pedido
```javascript
async function buscarPedido(pedidoId) {
  const response = await fetch(
    `http://localhost/backend/public/pedidos/${pedidoId}`
  );
  return await response.json();
}
```

---

## 📊 Fluxo Típico do App

```
1. Usuário abre o app
   ↓
2. Verifica se está autenticado (verifica localStorage)
   ├─ Se NÃO: mostra tela de login/registro
   │  └─ Usuario faz login/registro
   └─ Se SIM: continua
   ↓
3. Carrega lista de distribuidores (GET /distribuidores)
   ↓
4. Usuário seleciona distribuidor
   ↓
5. Carrega produtos (GET /distribuidores/{id}/produtos)
   ↓
6. Usuário seleciona produtos e quantidades
   ↓
7. Usuário confirma pedido
   ↓
8. Cria pedido (POST /pedidos)
   ↓
9. Mostra confirmação com ID do pedido
   ↓
10. Usuário pode consultar pedido (GET /pedidos/{id})
```

---

## 📝 Estrutura de Dados

### Usuário
```json
{
  "id_usuario": 1,
  "nome": "João Silva",
  "email": "joao@example.com",
  "telefone": "(11) 98765-4321",
  "ativo": true,
  "criado_em": "2024-05-05 10:30:00"
}
```

### Distribuidor
```json
{
  "id_distribuidor": 1,
  "nome_empresa": "Gás Brasil",
  "cnpj": "12.345.678/0001-99",
  "taxa_entrega": 10.00,
  "ativo": true
}
```

### Produto
```json
{
  "id_produto": 1,
  "tipo": "Botijão P13",
  "preco": 89.90,
  "estoque": 50
}
```

### Pedido
```json
{
  "id_pedido": 1,
  "id_usuario": 1,
  "id_distribuidor": 1,
  "total": 189.80,
  "itens": [
    {
      "id_produto": 1,
      "quantidade": 2,
      "preco_unitario": 89.90
    }
  ]
}
```

---

## 🐛 Códigos de Erro

| Código | Significado | Ação |
|--------|-------------|------|
| **200** | OK | Requisição bem-sucedida |
| **201** | Created | Recurso criado com sucesso |
| **400** | Bad Request | Dados inválidos ou incompletos |
| **401** | Unauthorized | Credenciais incorretas |
| **404** | Not Found | Recurso não encontrado |
| **409** | Conflict | Recurso já existe (ex: email duplicado) |
| **500** | Server Error | Erro no servidor |

---

## 📖 Visualizar API Interativamente

Você pode usar ferramentas como:
- **Swagger UI**: Cole o conteúdo de `swagger.json` no [editor online do Swagger](https://editor.swagger.io/)
- **Postman**: Importe o arquivo `swagger.json`
- **Insomnia**: Importe o arquivo `swagger.json`

---

## 🚀 Próximos Passos

- [ ] Implementar autenticação com tokens (JWT)
- [ ] Adicionar validação de endereço do usuário
- [ ] Filtrar distribuidores por proximidade
- [ ] Rastreamento de pedidos em tempo real
- [ ] Sistema de avaliação de distribuidores
- [ ] Integração com sistema de pagamento

---

**Última atualização:** 05/05/2024  
**Desenvolvido por:** Time de Desenvolvimento CadêGás
