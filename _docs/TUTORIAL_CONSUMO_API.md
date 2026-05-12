# 📚 Tutorial: Frontend Consumindo API REST HTTP/JSON - CadêGás

**Nível**: Iniciante  
**Duração**: 30-45 minutos  
**Objetivo**: Entender como o frontend comunica com o backend através da API REST descrita no `swagger.json`

---

## 📖 Índice

1. [Conceitos Fundamentais](#conceitos-fundamentais)
2. [Estrutura do Swagger.json](#estrutura-do-swaggerjon)
3. [Entendendo a Comunicação HTTP](#entendendo-a-comunicação-http)
4. [Cliente API (api-client.js)](#cliente-api-api-clientjs)
5. [Exemplo Prático: Fluxo de Login](#exemplo-prático-fluxo-de-login)
6. [Todos os Endpoints Explicados](#todos-os-endpoints-explicados)
7. [Tratamento de Erros](#tratamento-de-erros)
8. [Testando a API](#testando-a-api)

---

## 🎓 Conceitos Fundamentais

### O que é uma API REST?

Uma **API REST** (Representational State Transfer) é um conjunto de regras que permite que dois programas (frontend e backend) se comuniquem pela internet.

```
┌─────────────┐         HTTP/JSON         ┌─────────────┐
│  Frontend   │ ───────────────────────────▶  Backend    │
│ (JavaScript)│                            │   (PHP)     │
└─────────────┘                            └─────────────┘
   navegador                                  servidor
```

### O que é HTTP?

HTTP (HyperText Transfer Protocol) é o protocolo que define **como** enviar dados:

| Método | Uso | Exemplo |
|--------|-----|---------|
| **GET** | Buscar dados | `GET /distribuidores` (listar distribuidores) |
| **POST** | Enviar/criar dados | `POST /register` (criar novo usuário) |
| **PUT** | Atualizar dados | `PUT /pedidos/5` (atualizar pedido) |
| **DELETE** | Deletar dados | `DELETE /pedidos/5` (cancelar pedido) |

### O que é JSON?

JSON (JavaScript Object Notation) é um **formato de dados** que é fácil de ler e processado por máquinas:

```json
{
  "nome": "João Silva",
  "email": "joao@example.com",
  "telefone": "(11) 98765-4321"
}
```

---

## 📋 Estrutura do Swagger.json

O arquivo `swagger.json` é um **documento que descreve toda a API**. É como um manual que diz:

> "A API tem um endpoint `/register` que aceita um `POST` com campos `nome`, `email`, `telefone`, `senha`..."

### Estrutura Básica

```json
{
  "swagger": "2.0",
  "info": {
    "title": "CadêGás API",
    "version": "1.1.0"
  },
  "host": "localhost",
  "basePath": "/cadegas/backend/public",
  "paths": {
    "/register": {
      "post": { /* descrição do endpoint */ }
    }
  }
}
```

### O que significa cada parte?

| Campo | Significado | Exemplo |
|-------|------------|---------|
| `host` | Domínio do servidor | `localhost` |
| `basePath` | Caminho base da API | `/cadegas/backend/public` |
| `paths` | Lista de endpoints disponíveis | `/register`, `/login`, `/pedidos` |
| `POST` / `GET` | Tipo de requisição HTTP | POST para criar, GET para buscar |

### Definições (Schemas)

As definições descrevem a **estrutura dos dados**:

```json
"RegistroRequest": {
  "type": "object",
  "required": ["nome", "email", "telefone", "senha"],
  "properties": {
    "nome": { "type": "string", "example": "João Silva" },
    "email": { "type": "string", "format": "email" },
    "senha": { "type": "string", "minLength": 6 }
  }
}
```

**Tradução**: 
- É um objeto (JSON)
- Campos obrigatórios: `nome`, `email`, `telefone`, `senha`
- `nome` é texto
- `email` é texto no formato de e-mail
- `senha` deve ter mínimo 6 caracteres

---

## 🌐 Entendendo a Comunicação HTTP

### Anatomia de uma Requisição

Quando o frontend quer fazer login, ele envia:

```http
POST /cadegas/backend/public/login HTTP/1.1
Host: localhost
Content-Type: application/json

{
  "email": "joao@example.com",
  "senha": "senha123"
}
```

**Quebrando para entender:**

```
┌─ Método HTTP: POST (estou mandando dados)
│
POST /cadegas/backend/public/login HTTP/1.1
│                                 └─ Caminho do endpoint
│
Host: localhost
└─ Servidor onde mandar

Content-Type: application/json
└─ Formato dos dados (JSON)

{
  "email": "joao@example.com",
  "senha": "senha123"
}
└─ Corpo da requisição (o que estou enviando)
```

### Anatomia de uma Resposta

O backend responde com:

```http
HTTP/1.1 200 OK
Content-Type: application/json

{
  "mensagem": "Login realizado com sucesso",
  "id_usuario": 1,
  "nome": "João Silva",
  "email": "joao@example.com"
}
```

**Quebrando para entender:**

```
HTTP/1.1 200 OK
├─ 200 = Sucesso! (código de status HTTP)

Content-Type: application/json
└─ A resposta está em formato JSON

{
  "mensagem": "Login realizado com sucesso",
  "id_usuario": 1,
  ...
}
└─ Corpo da resposta (o que o servidor retorna)
```

### Códigos de Status HTTP

| Código | Significado | Exemplo |
|--------|-------------|---------|
| **200** | ✅ Sucesso | Login realizado |
| **201** | ✅ Criado com sucesso | Usuário cadastrado |
| **400** | ❌ Requisição inválida | Faltou preencher um campo |
| **401** | ❌ Não autorizado | Email ou senha errados |
| **404** | ❌ Não encontrado | Pedido não existe |
| **409** | ❌ Conflito | Email já cadastrado |
| **500** | ❌ Erro no servidor | Bug no PHP |

---

## 💻 Cliente API (api-client.js)

O arquivo `api-client.js` é a **camada de comunicação** entre frontend e backend. É a responsável por:

1. Formatar os dados para JSON
2. Enviar a requisição HTTP
3. Processar a resposta
4. Gerenciar o ID do usuário logado

### Estrutura Principal

```javascript
class ApiClient {
  constructor(config) {
    this.baseUrl = config.BASE_URL;  // http://localhost/cadegas/backend/public
    this.timeout = config.TIMEOUT;    // 30000ms = 30 segundos
  }

  async request(endpoint, options = {}) {
    // Montar URL completa
    const url = `${this.baseUrl}${endpoint}`;
    // http://localhost/cadegas/backend/public + /login
    // = http://localhost/cadegas/backend/public/login

    // Enviar requisição fetch
    const response = await fetch(url, {
      method: options.method || 'GET',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(options.data)
    });

    // Processar resposta
    const data = await response.json();
    return data;
  }
}
```

### Como é usado no Frontend

```javascript
// Importar o cliente
<script src="api-client.js"></script>

// Agora temos disponível a instância global:
window.api

// Usar em qualquer lugar:
const resultado = await api.login("joao@example.com", "senha123");
console.log(resultado);
// Saída: { mensagem: "Login realizado com sucesso", id_usuario: 1, ... }
```

---

## 🔑 Exemplo Prático: Fluxo de Login

### Passo 1: Frontend coleta dados

No HTML, o usuário preenche um formulário:

```html
<form id="loginForm">
  <input type="email" id="email" placeholder="Email">
  <input type="password" id="senha" placeholder="Senha">
  <button type="submit">Login</button>
</form>
```

### Passo 2: JavaScript captura o submit

```javascript
document.getElementById('loginForm').addEventListener('submit', async (e) => {
  e.preventDefault(); // Impedir recarga da página

  const email = document.getElementById('email').value;
  const senha = document.getElementById('senha').value;

  // Chamar a API
  const resultado = await api.login(email, senha);

  if (resultado.id_usuario) {
    // ✅ Login bem-sucedido!
    console.log("Bem-vindo,", resultado.nome);
    window.location.hash = '#distribuidores';
  } else {
    // ❌ Erro
    alert(resultado.erro);
  }
});
```

### Passo 3: api-client.js prepara e envia

```javascript
async login(email, senha) {
  // Chamar método POST
  const response = await this.post('/login', {
    email,      // joao@example.com
    senha       // senha123
  });

  // Se sucesso, guardar ID do usuário
  if (response.id_usuario) {
    this.saveUserId(response.id_usuario);
  }

  return response;
}

async post(endpoint, data) {
  return this.request(endpoint, {
    method: 'POST',
    data: data  // { email, senha }
  });
}

async request(endpoint, options = {}) {
  const url = `${this.baseUrl}${endpoint}`;
  // http://localhost/cadegas/backend/public/login

  const response = await fetch(url, {
    method: options.method,  // POST
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(options.data)
    // { "email": "joao@example.com", "senha": "senha123" }
  });

  return await response.json();
}
```

### Passo 4: Backend recebe, processa e responde

PHP (`backend/controllers/AuthController.php`):

```php
public function login()
{
    // ✅ Receber dados JSON
    $data = json_decode(file_get_contents("php://input"), true);
    // $data = ["email" => "joao@example.com", "senha" => "senha123"]

    $email = $data['email'];
    $senha = $data['senha'];

    // ✅ Validar
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(["erro" => "E-mail inválido"]);
        return;
    }

    // ✅ Buscar usuário no banco
    $usuario = $usuarioModel->buscarPorEmail($email);

    // ✅ Verificar senha com bcrypt
    if (!password_verify($senha, $usuario['senha'])) {
        http_response_code(401);
        echo json_encode(["erro" => "E-mail ou senha inválidos"]);
        return;
    }

    // ✅ Responder com sucesso
    http_response_code(200);
    echo json_encode([
        "mensagem" => "Login realizado com sucesso",
        "id_usuario" => $usuario['id_usuario'],
        "nome" => $usuario['nome'],
        "email" => $usuario['email']
    ]);
    // Retorna JSON para o frontend
}
```

### Passo 5: Frontend recebe e processa

A resposta retorna para o JavaScript:

```javascript
{
  "mensagem": "Login realizado com sucesso",
  "id_usuario": 1,
  "nome": "João Silva",
  "email": "joao@example.com"
}

// Frontend agora pode usar os dados:
if (resultado.id_usuario) {
  window.location.hash = '#distribuidores';  // Ir para próxima página
}
```

### 🎯 Resumo Visual do Fluxo

```
1. FRONTEND (navegador)
   ↓ Usuário clica em "Login"
   ↓ HTML captura email/senha
   ↓ JavaScript chama api.login()
   
2. API-CLIENT.JS
   ↓ Formata dados em JSON
   ↓ Envia POST para o servidor
   
3. HTTP (protocolo de comunicação)
   ↓ POST http://localhost/cadegas/backend/public/login
   ↓ Body: { "email": "...", "senha": "..." }
   
4. BACKEND (PHP)
   ↓ Recebe requisição
   ↓ Decodifica JSON
   ↓ Valida dados
   ↓ Busca no banco de dados
   ↓ Verifica senha
   ↓ Retorna resposta JSON
   
5. HTTP (protocolo de comunicação)
   ↓ Response: { "mensagem": "...", "id_usuario": 1 }
   
6. FRONTEND (navegador)
   ↓ Recebe resposta JSON
   ↓ Processa dados
   ↓ Armazena ID do usuário
   ↓ Redireciona para próxima página
```

---

## 📡 Todos os Endpoints Explicados

### 1️⃣ Registro (POST /register)

**Para quê?** Criar uma conta nova

**Dados que enviam:**
```json
{
  "nome": "João Silva",
  "email": "joao@example.com",
  "telefone": "(11) 98765-4321",
  "senha": "senha123",
  "endereco": "Av. Paulista, 1000",  // Opcional
  "cidade": "São Paulo",              // Opcional
  "estado": "SP",                      // Opcional
  "cep": "01310-100"                   // Opcional
}
```

**Validações no Backend:**
- ✅ Email obrigatório e válido
- ✅ Senha mínimo 6 caracteres
- ✅ Email não pode ser duplicado (se existir, erro 409)

**Resposta de Sucesso (201):**
```json
{
  "mensagem": "Usuário cadastrado com sucesso",
  "id_usuario": 1,
  "email": "joao@example.com"
}
```

**Como usar no Frontend:**
```javascript
const resultado = await api.register(
  "João Silva",
  "joao@example.com",
  "(11) 98765-4321",
  "senha123"
);

if (resultado.id_usuario) {
  console.log("✅ Usuário criado com ID:", resultado.id_usuario);
}
```

---

### 2️⃣ Login (POST /login)

**Para quê?** Fazer login e obter ID do usuário

**Dados que enviam:**
```json
{
  "email": "joao@example.com",
  "senha": "senha123"
}
```

**Resposta de Sucesso (200):**
```json
{
  "mensagem": "Login realizado com sucesso",
  "id_usuario": 1,
  "nome": "João Silva",
  "email": "joao@example.com"
}
```

**Como usar:**
```javascript
const resultado = await api.login("joao@example.com", "senha123");

if (resultado.id_usuario) {
  // ✅ Login bem-sucedido
  // api-client.js automaticamente salvará o ID no localStorage
  api.saveUserId(resultado.id_usuario);
}
```

---

### 3️⃣ Listar Distribuidores (GET /distribuidores)

**Para quê?** Mostrar todas as distribuidoras disponíveis

**Dados que enviam:** NENHUM

**Resposta de Sucesso (200):**
```json
[
  {
    "id_distribuidor": 1,
    "nome_empresa": "Distribuidora Rápida",
    "cnpj": "12.345.678/0001-90",
    "telefone": "(11) 98765-4321",
    "endereco": "Rua das Flores, 123",
    "cidade": "São Paulo",
    "estado": "SP",
    "taxa_entrega": 10.00,
    "ativo": 1
  },
  {
    "id_distribuidor": 2,
    "nome_empresa": "GasExpress",
    "cnpj": "98.765.432/0001-12",
    "telefone": "(11) 91234-5678",
    "endereco": "Av. Paulista, 1000",
    "cidade": "São Paulo",
    "estado": "SP",
    "taxa_entrega": 8.00,
    "ativo": 1
  }
]
```

**Como usar:**
```javascript
const distribuidores = await api.listDistribuidores();

// Mostrar no HTML
distribuidores.forEach(dist => {
  console.log(`${dist.nome_empresa} - Taxa: R$${dist.taxa_entrega}`);
});
```

---

### 4️⃣ Produtos de um Distribuidor (GET /distribuidores/{id}/produtos)

**Para quê?** Mostrar produtos de uma distribuidora específica

**Dados que enviam:** 
- `id` na URL (ex: `/distribuidores/1/produtos`)

**Resposta de Sucesso (200):**
```json
{
  "distribuidor_id": 1,
  "produtos": [
    {
      "id_produto": 1,
      "id_distribuidor": 1,
      "nome": "Botijão P13 (13 kg)",
      "descricao": "Botijão residencial padrão",
      "preco": 135.00,
      "disponivel": 1
    },
    {
      "id_produto": 2,
      "id_distribuidor": 1,
      "nome": "Galão de Água 20L",
      "descricao": "Galão de água mineral 20 litros",
      "preco": 18.00,
      "disponivel": 1
    }
  ]
}
```

**Como usar:**
```javascript
const produtos = await api.listProdutos(1);  // ID da distribuidora

produtos.forEach(prod => {
  console.log(`${prod.nome} - R$${prod.preco}`);
});
```

---

### 5️⃣ Todos os Produtos (GET /produtos)

**Para quê?** Listar TODOS os produtos de TODAS as distribuidoras ativas

**Dados que enviam:** NENHUM

**Resposta de Sucesso (200):**
```json
{
  "produtos": [
    {
      "id_produto": 1,
      "id_distribuidor": 1,
      "nome": "Botijão P13 (13 kg)",
      "preco": 135.00,
      "disponivel": 1,
      "nome_empresa": "Distribuidora Rápida",
      "taxa_entrega": 10.00
    },
    {
      "id_produto": 3,
      "id_distribuidor": 2,
      "nome": "Botijão P13 (13 kg)",
      "preco": 130.00,
      "disponivel": 1,
      "nome_empresa": "GasExpress",
      "taxa_entrega": 8.00
    }
  ]
}
```

**Como usar:**
```javascript
const todosProdutos = await api.listAllProdutos();

// Filtrar apenas produtos da distribuidora 1
const produtosDist1 = todosProdutos.filter(p => p.id_distribuidor === 1);
```

---

### 6️⃣ Criar Pedido (POST /pedidos)

**Para quê?** Fazer um novo pedido

**Dados que enviam:**
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
  ],
  "forma_pagamento": "pix",
  "endereco_entrega": "Av. Principal, 50"  // Opcional
}
```

**Validações no Backend:**
- ✅ Usuário deve existir
- ✅ Distribuidor deve existir e estar ativo
- ✅ Produtos devem estar disponíveis
- ✅ Produtos devem pertencer ao distribuidor informado
- ✅ Forma de pagamento: `dinheiro`, `pix` ou `cartao`

**Resposta de Sucesso (201):**
```json
{
  "mensagem": "Pedido criado com sucesso",
  "id_pedido": 5,
  "subtotal": 215.00,
  "taxa_entrega": 10.00,
  "total": 225.00,
  "forma_pagamento": "pix",
  "status": "pendente"
}
```

**Como usar:**
```javascript
const idUsuario = api.getUserId();  // Recuperar do localStorage

const resultado = await api.criarPedido(
  1,  // ID distribuidor
  [
    { id_produto: 1, quantidade: 2 },
    { id_produto: 2, quantidade: 1 }
  ],
  'pix'  // forma_pagamento
);

if (resultado.id_pedido) {
  console.log(`✅ Pedido ${resultado.id_pedido} criado! Total: R$${resultado.total}`);
}
```

---

### 7️⃣ Buscar Detalhes do Pedido (GET /pedidos/{id})

**Para quê?** Ver informações completas de um pedido

**Dados que enviam:**
- `id` na URL (ex: `/pedidos/5`)

**Resposta de Sucesso (200):**
```json
{
  "pedido": {
    "id_pedido": 5,
    "id_usuario": 1,
    "id_distribuidor": 1,
    "status": "pendente",
    "subtotal": 215.00,
    "taxa_entrega": 10.00,
    "total": 225.00,
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
    },
    {
      "id_produto": 2,
      "nome": "Galão de Água 20L",
      "descricao": "Galão de água mineral 20 litros",
      "quantidade": 1,
      "preco_unitario": 25.00,
      "subtotal": 25.00
    }
  ],
  "mensagem": "O distribuidor entrará em contato para confirmar a entrega"
}
```

**Como usar:**
```javascript
const pedido = await api.buscarPedido(5);

console.log(`Status: ${pedido.pedido.status}`);
console.log(`Total: R$${pedido.pedido.total}`);

pedido.itens.forEach(item => {
  console.log(`- ${item.nome}: ${item.quantidade}x R$${item.preco_unitario}`);
});
```

---

## ⚠️ Tratamento de Erros

### Tipos de Erro

**Erro 400 - Requisição Inválida**
```javascript
// O que causou?
// - Faltou preencher campo obrigatório
// - Formato de dados inválido
// - Email inválido
// - Senha < 6 caracteres

const resultado = await api.login("", "");
// resultado = { erro: "E-mail e senha são obrigatórios" }
```

**Erro 401 - Não Autorizado**
```javascript
// O que causou?
// - Email ou senha errados no login
// - Usuário não existe
// - ID de usuário inválido no pedido

const resultado = await api.login("joao@example.com", "senhaerrada");
// resultado = { erro: "E-mail ou senha inválidos" }
```

**Erro 404 - Não Encontrado**
```javascript
// O que causou?
// - Pedido/produto/distribuidor não existe

const resultado = await api.buscarPedido(999);
// resultado = { erro: "Pedido não encontrado" }
```

**Erro 409 - Conflito**
```javascript
// O que causou?
// - Email já cadastrado
// - Distribuidor inativo
// - Produto indisponível

const resultado = await api.register("João", "email@ja.existe.com", "(11) 98765-4321", "senha123");
// resultado = { erro: "E-mail já cadastrado" }
```

**Erro 500 - Erro no Servidor**
```javascript
// O que causou?
// - Bug no PHP
// - Erro no banco de dados
// - Exceção não tratada
```

### Como Tratar Erros no Frontend

```javascript
try {
  const resultado = await api.login(email, senha);

  if (!resultado.id_usuario) {
    // ❌ Houve erro
    console.error("Erro:", resultado.erro);
    alert(resultado.erro);  // Mostrar mensagem para usuário
  } else {
    // ✅ Sucesso
    console.log("Bem-vindo!");
    window.location.hash = '#distribuidores';
  }

} catch (error) {
  // ❌ Erro de conexão ou timeout
  console.error("Erro ao conectar:", error.message);
  alert("Não foi possível conectar ao servidor");
}
```

### Estrutura de Resposta de Erro

```json
{
  "erro": "Mensagem descritiva do que deu errado"
}
```

**No Frontend:**
```javascript
const resultado = await api.request(...);

if (resultado.erro) {
  // É um erro
  console.error(resultado.erro);
} else {
  // É sucesso
  console.log(resultado);
}
```

---

## 🧪 Testando a API

### Opção 1: Usar o Console do Navegador

**Passo 1:** Abrir o navegador (Chrome, Firefox)

**Passo 2:** Pressionar `F12` para abrir DevTools

**Passo 3:** Ir na aba "Console"

**Passo 4:** Testar um endpoint:

```javascript
// Listar distribuidores
api.listDistribuidores().then(result => console.log(result));

// Fazer login
api.login("teste@email.com", "123456").then(result => console.log(result));

// Ver ID do usuário armazenado
api.getUserId()
```

### Opção 2: Usar Postman

**Postman** é uma ferramenta que permite testar APIs de forma visual.

**Passo 1:** Baixar em [postman.com](https://www.postman.com)

**Passo 2:** Abrir e criar nova requisição

**Passo 3:** Configurar:
- **Method**: POST
- **URL**: `http://localhost/cadegas/backend/public/login`
- **Headers**: `Content-Type: application/json`
- **Body** (raw, JSON):
  ```json
  {
    "email": "teste@email.com",
    "senha": "123456"
  }
  ```

**Passo 4:** Clicar em "Send"

**Resultado:**
```json
{
  "mensagem": "Login realizado com sucesso",
  "id_usuario": 1,
  "nome": "Usuário Teste",
  "email": "teste@email.com"
}
```

### Opção 3: Usar curl (Terminal/PowerShell)

```bash
# Login
curl -X POST http://localhost/cadegas/backend/public/login \
  -H "Content-Type: application/json" \
  -d '{"email":"teste@email.com","senha":"123456"}'

# Listar distribuidores
curl http://localhost/cadegas/backend/public/distribuidores
```

### Opção 4: Adicionar Debug no Frontend

Adicione logs no `api-client.js`:

```javascript
async request(endpoint, options = {}) {
  const url = `${this.baseUrl}${endpoint}`;
  
  console.log("🔄 Enviando requisição:");
  console.log("  URL:", url);
  console.log("  Método:", options.method);
  console.log("  Dados:", options.data);
  
  const response = await fetch(url, {
    method: options.method || 'GET',
    headers: { 'Content-Type': 'application/json' },
    body: options.data ? JSON.stringify(options.data) : undefined
  });

  const data = await response.json();
  
  console.log("✅ Resposta recebida:");
  console.log("  Status:", response.status);
  console.log("  Dados:", data);
  
  return data;
}
```

---

## 📊 Fluxo Completo: Do Registro ao Primeiro Pedido

### Cenário: João quer pedir um botijão de gás

```
PASSO 1: REGISTRO
┌─ Frontend: João preenche formulário de registro
│  └─ api.register("João", "joao@email.com", "(11) 98765-4321", "senha123")
├─ Backend: Cria usuário no banco
└─ Resposta: { id_usuario: 1 }

PASSO 2: LOGIN
┌─ Frontend: João faz login
│  └─ api.login("joao@email.com", "senha123")
├─ Backend: Valida credenciais
├─ Resposta: { id_usuario: 1, nome: "João", ... }
└─ Frontend: Salva ID no localStorage

PASSO 3: LISTAR DISTRIBUIDORAS
┌─ Frontend: Carrega lista de distribuidoras
│  └─ api.listDistribuidores()
├─ Backend: Retorna todas as ativas
└─ Resposta: [ { id: 1, nome_empresa: "Distribuidora Rápida", ... } ]

PASSO 4: VER PRODUTOS DA DISTRIBUIDORA
┌─ Frontend: João clica em uma distribuidora
│  └─ api.listProdutos(1)
├─ Backend: Retorna produtos da distribuidora 1
└─ Resposta: { distribuidor_id: 1, produtos: [...] }

PASSO 5: CRIAR PEDIDO
┌─ Frontend: João seleciona 2 botijões e clica "Comprar"
│  └─ api.criarPedido(1, [{id_produto: 1, quantidade: 2}], "pix")
├─ Backend: 
│  ├─ Valida usuário
│  ├─ Valida distribuidor
│  ├─ Valida produtos
│  ├─ Calcula preços
│  └─ Cria pedido no banco
└─ Resposta: { id_pedido: 5, total: 225.00, status: "pendente" }

PASSO 6: VER DETALHES DO PEDIDO
┌─ Frontend: João quer ver o recibo
│  └─ api.buscarPedido(5)
├─ Backend: Retorna pedido completo com itens
└─ Resposta: { pedido: {...}, itens: [...] }
```

---

## 🎯 Checklist de Entendimento

Após ler este tutorial, você deve ser capaz de:

- [ ] Explicar o que é HTTP e quais são os métodos (GET, POST)
- [ ] Entender o que é JSON e como estruturá-lo
- [ ] Ler um arquivo swagger.json e identificar endpoints
- [ ] Entender a diferença entre `basePath` e `paths`
- [ ] Entender como o frontend envia dados para o backend
- [ ] Entender como o backend responde com JSON
- [ ] Usar a classe `ApiClient` para fazer requisições
- [ ] Identificar códigos de erro HTTP (400, 401, 404, 409, 500)
- [ ] Testar uma API usando o console do navegador
- [ ] Descrever o fluxo completo de login → pedido

---

## 📚 Recursos Adicionais

### Documentação Oficial
- [MDN - HTTP](https://developer.mozilla.org/pt-BR/docs/Web/HTTP)
- [MDN - Fetch API](https://developer.mozilla.org/pt-BR/docs/Web/API/Fetch_API)
- [JSON.org](https://www.json.org/json-en.html)
- [Swagger.io](https://swagger.io/)

### Ferramentas Úteis
- **Postman**: Testar APIs (https://www.postman.com)
- **curl**: Testar via terminal
- **DevTools do Navegador**: Aba Network para ver requisições

### Próximos Passos
1. Tentar fazer uma requisição manual no console
2. Modificar o `api-client.js` para adicionar logs
3. Testar cada endpoint com Postman
4. Implementar novas funcionalidades na API

---

## ❓ Perguntas Frequentes

**P: Por que o frontend armazena o ID do usuário no localStorage?**  
R: Porque o HTTP é "sem estado" (stateless). A cada requisição, o servidor não lembra quem fez a requisição anterior. O frontend precisa guardar e enviar o ID do usuário em cada pedido.

**P: Posso usar a API do navegador de outro domínio?**  
R: Sim, mas o backend precisa permitir CORS (Cross-Origin Resource Sharing). O arquivo `config.php` já inclui os headers CORS.

**P: Qual é a diferença entre json_encode() no PHP e JSON no JavaScript?**  
R: Nenhuma para dados simples. `json_encode()` converte um array PHP para string JSON. `JSON.stringify()` faz o mesmo no JavaScript.

**P: Por que usar async/await em vez de .then()?**  
R: async/await é mais legível e fácil de entender. É a forma moderna de trabalhar com Promises em JavaScript.

**P: Se a senha for 123456, é segura?**  
R: Não! O backend usa bcrypt para criptografar, mas senhas fracas são fáceis de quebrar. Sempre use senhas fortes.

---

**Última atualização**: 6 de maio de 2026  
**Versão do Tutorial**: 1.0  
**Versão da API**: 1.1.0  
