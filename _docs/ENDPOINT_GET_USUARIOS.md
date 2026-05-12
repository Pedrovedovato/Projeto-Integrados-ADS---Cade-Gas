# Especificação: GET /usuarios/{id}

## 📋 Informações Gerais

**Endpoint:** `/usuarios/{id}`  
**Método:** `GET`  
**Autenticação:** Opcional (recomendado verificar se usuário está logado)  
**Descrição:** Retorna os dados completos do perfil de um usuário

---

## 🎯 Propósito

Este endpoint resolve o **Issue #4** documentado em `ISSUES_SWAGGER_1.md`:

**Problema:**
- LoginResponse retorna apenas: `{mensagem, id_usuario, nome, email, usuario: {id, nome, email}}`
- Não retorna: `telefone`, `endereco`, `cidade`, `estado`, `cep`
- Frontend não tem dados completos para exibir no checkout

**Solução:**
- Endpoint permite buscar perfil completo após login
- Frontend pode popular localStorage com todos os dados
- Melhora UX no checkout (exibe dados completos)

---

## 📥 Request

### URL
```
GET /cadegas/backend/public/usuarios/{id}
```

### Path Parameters

| Parâmetro | Tipo | Obrigatório | Descrição |
|-----------|------|-------------|-----------|
| `id` | integer | Sim | ID do usuário (id_usuario) |

### Headers
```
Content-Type: application/json
```

### Exemplo de Request
```bash
curl -X GET http://localhost/cadegas/backend/public/usuarios/1 \
  -H "Content-Type: application/json"
```

---

## 📤 Response

### Success (200 OK)

**Schema:**
```json
{
  "id_usuario": integer,
  "nome": string,
  "email": string,
  "telefone": string,
  "endereco": string,
  "cidade": string,
  "estado": string,
  "cep": string
}
```

**Exemplo:**
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

### Error (404 Not Found)

**Schema:**
```json
{
  "erro": string
}
```

**Exemplo:**
```json
{
  "erro": "Usuário não encontrado"
}
```

---

## 🔧 Implementação do Controller (PHP)

### Localização do arquivo
```
/cadegas/backend/public/usuarios.php
```

### Código do Controller

```php
<?php
/**
 * GET /usuarios/{id}
 * Busca perfil completo de um usuário
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// Incluir configuração e conexão com banco
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';

// Verificar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['erro' => 'Método não permitido'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Extrair ID da URL
// Formato esperado: /usuarios/{id} ou /usuarios.php?id={id}
$id_usuario = null;

// Opção 1: Via query parameter (GET /usuarios.php?id=1)
if (isset($_GET['id'])) {
    $id_usuario = intval($_GET['id']);
}

// Opção 2: Via path info (GET /usuarios/1)
// Requer configuração de rewrite no .htaccess
if (!$id_usuario && isset($_SERVER['PATH_INFO'])) {
    $path_parts = explode('/', trim($_SERVER['PATH_INFO'], '/'));
    if (!empty($path_parts[0])) {
        $id_usuario = intval($path_parts[0]);
    }
}

// Validar ID
if (!$id_usuario || $id_usuario <= 0) {
    http_response_code(400);
    echo json_encode(['erro' => 'ID de usuário inválido'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    // Obter conexão com banco
    $db = BancoDados::obterInstancia();
    $conn = $db->obterConexao();
    
    // Preparar query
    $sql = "SELECT 
                id_usuario,
                nome,
                email,
                telefone,
                endereco,
                cidade,
                estado,
                cep
            FROM usuario 
            WHERE id_usuario = :id_usuario";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt->execute();
    
    // Buscar usuário
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        http_response_code(404);
        echo json_encode(['erro' => 'Usuário não encontrado'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Converter id_usuario para integer
    $usuario['id_usuario'] = intval($usuario['id_usuario']);
    
    // Garantir que campos nulos sejam strings vazias
    $usuario['telefone'] = $usuario['telefone'] ?? '';
    $usuario['endereco'] = $usuario['endereco'] ?? '';
    $usuario['cidade'] = $usuario['cidade'] ?? '';
    $usuario['estado'] = $usuario['estado'] ?? '';
    $usuario['cep'] = $usuario['cep'] ?? '';
    
    // Retornar sucesso
    http_response_code(200);
    echo json_encode($usuario, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'erro' => 'Erro ao buscar usuário'
    ], JSON_UNESCAPED_UNICODE);
    
    // Log do erro (em produção, use sistema de log apropriado)
    error_log('Erro ao buscar usuário: ' . $e->getMessage());
}
?>
```

---

## 🔀 Configuração de Rotas (.htaccess)

Para suportar URLs limpas como `/usuarios/1`, adicione ao `.htaccess`:

```apache
# Rota para GET /usuarios/{id}
RewriteRule ^usuarios/([0-9]+)$ usuarios.php?id=$1 [L,QSA]
```

**Localização:** `/cadegas/backend/public/.htaccess`

**Arquivo completo:**
```apache
# Backend CadêGás - Rotas API

RewriteEngine On
RewriteBase /cadegas/backend/public

# Rota: GET /usuarios/{id}
RewriteRule ^usuarios/([0-9]+)$ usuarios.php?id=$1 [L,QSA]

# Rota: GET /distribuidores/{id}/produtos
RewriteRule ^distribuidores/([0-9]+)/produtos$ distribuidores_produtos.php?id=$1 [L,QSA]

# Rota: GET /pedidos/{id}
RewriteRule ^pedidos/([0-9]+)$ pedidos_detalhes.php?id=$1 [L,QSA]
```

---

## 🧪 Testes

### Teste 1: Usuário existente
```bash
curl -X GET http://localhost/cadegas/backend/public/usuarios/1
```

**Resposta esperada (200):**
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

### Teste 2: Usuário inexistente
```bash
curl -X GET http://localhost/cadegas/backend/public/usuarios/999
```

**Resposta esperada (404):**
```json
{
  "erro": "Usuário não encontrado"
}
```

### Teste 3: ID inválido
```bash
curl -X GET http://localhost/cadegas/backend/public/usuarios/abc
```

**Resposta esperada (400):**
```json
{
  "erro": "ID de usuário inválido"
}
```

---

## 🔄 Integração com Frontend

### Atualizar api.js

Adicione método ao módulo Auth:

```javascript
// Em frontend/assets/js/api.js

const Auth = {
    // ... métodos existentes (registrar, login)
    
    /**
     * Buscar perfil completo do usuário
     * GET /usuarios/{id}
     * @param {number} id - ID do usuário
     * @returns {Promise} Dados completos do usuário
     */
    async buscarPerfil(id) {
        return apiRequest(`/usuarios/${id}`);
    }
};
```

### Atualizar login.php

Após login bem-sucedido, buscar perfil completo:

```javascript
// Em frontend/pages/login.php

const resultado = await API.Auth.login(email, senha);

if (resultado.sucesso) {
    const id_usuario = resultado.id_usuario;
    
    // Buscar perfil completo
    const perfil = await API.Auth.buscarPerfil(id_usuario);
    
    if (perfil.sucesso) {
        // Salvar perfil completo no localStorage
        localStorage.setItem('usuario', JSON.stringify(perfil));
        window.location.href = 'home.php';
    } else {
        // Se falhar ao buscar perfil, usar dados do login
        const usuario = {
            id_usuario: resultado.id_usuario,
            nome: resultado.nome,
            email: resultado.email,
            telefone: '',
            endereco: '',
            cidade: '',
            estado: '',
            cep: ''
        };
        localStorage.setItem('usuario', JSON.stringify(usuario));
        window.location.href = 'home.php';
    }
}
```

---

## 📊 Diagrama de Fluxo

```
┌─────────────┐
│   Cliente   │
│  (Browser)  │
└──────┬──────┘
       │
       │ 1. POST /login
       ├────────────────────────────────┐
       │                                │
       │ 2. {id_usuario: 1, ...}        │
       │◄───────────────────────────────┤
       │                                │
       │ 3. GET /usuarios/1             │
       ├────────────────────────────────┤
       │                                │
       │ 4. {perfil completo}           │
       │◄───────────────────────────────┤
       │                                │
       │ 5. localStorage.setItem(...)   │
       │                                │
       │ 6. Navegar para home.php       │
       │                           ┌────▼─────┐
       │                           │  Backend │
       │                           │    API   │
       │                           └──────────┘
```

---

## 🔐 Segurança

### Recomendações

1. **Autenticação:** Verificar se usuário está logado antes de retornar dados
2. **Autorização:** Usuário só pode buscar seu próprio perfil
3. **Validação:** Validar e sanitizar ID do usuário
4. **Logs:** Registrar tentativas de acesso a perfis de outros usuários

### Implementação de Autorização (Opcional)

```php
// Verificar se usuário está logado e autorizado
session_start();

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['erro' => 'Não autorizado'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Verificar se usuário está tentando acessar seu próprio perfil
if ($_SESSION['usuario_id'] != $id_usuario) {
    http_response_code(403);
    echo json_encode(['erro' => 'Acesso negado'], JSON_UNESCAPED_UNICODE);
    exit;
}
```

---

## 📝 Adição ao swagger.json (Opcional)

Para documentar no Swagger, adicione:

```json
"/usuarios/{id}": {
  "get": {
    "tags": ["Usuários"],
    "summary": "Buscar perfil completo do usuário",
    "description": "Retorna todos os dados do perfil de um usuário específico",
    "operationId": "buscarUsuario",
    "parameters": [
      {
        "name": "id",
        "in": "path",
        "description": "ID do usuário",
        "required": true,
        "type": "integer",
        "format": "int32"
      }
    ],
    "responses": {
      "200": {
        "description": "Dados do usuário",
        "schema": { "$ref": "#/definitions/UsuarioPerfil" }
      },
      "404": {
        "description": "Usuário não encontrado",
        "schema": { "$ref": "#/definitions/Erro" }
      }
    }
  }
}
```

**Definição:**
```json
"UsuarioPerfil": {
  "type": "object",
  "properties": {
    "id_usuario": { "type": "integer", "format": "int32", "example": 1 },
    "nome": { "type": "string", "example": "João Silva" },
    "email": { "type": "string", "format": "email", "example": "joao@example.com" },
    "telefone": { "type": "string", "example": "(11) 98765-4321" },
    "endereco": { "type": "string", "example": "Av. Paulista, 1000" },
    "cidade": { "type": "string", "example": "São Paulo" },
    "estado": { "type": "string", "example": "SP" },
    "cep": { "type": "string", "example": "01310-100" }
  }
}
```

---

## 📋 Checklist de Implementação

### Backend
- [ ] Criar arquivo `/backend/public/usuarios.php`
- [ ] Implementar query SELECT no banco
- [ ] Adicionar tratamento de erros
- [ ] Configurar rotas no `.htaccess`
- [ ] Testar com curl (200, 404, 400)
- [ ] (Opcional) Adicionar autenticação/autorização
- [ ] (Opcional) Atualizar swagger.json

### Frontend
- [ ] Adicionar método `buscarPerfil()` em `api.js`
- [ ] Atualizar `login.php` para buscar perfil após login
- [ ] Testar fluxo completo (login → buscar perfil → checkout)
- [ ] Verificar se dados aparecem corretamente no checkout

---

**Data:** 11/05/2026  
**Versão:** 1.0  
**Status:** ✅ Especificação completa  
**Implementação:** Aguardando backend
