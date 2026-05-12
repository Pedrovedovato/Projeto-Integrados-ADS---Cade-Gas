# Backend API (MVP)

Backend da API REST do projeto CadêGás, desenvolvido em PHP + MySQL, com foco em validar um MVP (Produto Mínimo Viável).

O sistema permite:

- Cadastro e login de usuários
- Busca do perfil completo do usuário
- Listagem de distribuidores
- Listagem de produtos (geral ou por distribuidor)
- Criação e consulta de pedidos

## Objetivo do Projeto

Validar a proposta de valor de um sistema que conecta consumidores a distribuidores de gás e água, permitindo pedidos simples e confirmação offline. O projeto **não** possui funcionalidades avançadas como pagamento online ou rastreamento em tempo real — essas estão fora do escopo do MVP.

## Tecnologias

- PHP 7+
- MySQL 5.7+
- PDO
- UwAmp (Apache + MySQL + PHP no Windows)
- Postman (para testar endpoints)
- Arquitetura MVC simples (sem framework, sem Composer)
- API REST (JSON)

## Estrutura do projeto

```
cadegas/backend/
├── config.php                   # carrega o .env em $_ENV
├── routes.php                   # roteamento por if/preg_match
├── config/
│   └── database.php             # singleton PDO (Database::connect)
├── controllers/
│   ├── AuthController.php       # POST /register, POST /login
│   ├── UsuarioController.php    # GET /usuarios/{id}
│   ├── DistribuidorController.php  # GET /distribuidores, GET /distribuidores/{id}/produtos
│   ├── ProdutoController.php    # GET /produtos
│   └── PedidoController.php     # POST /pedidos, GET /pedidos/{id}
├── models/
│   ├── Usuario.php
│   ├── Distribuidor.php
│   ├── Produto.php
│   └── Pedido.php
├── public/
│   ├── index.php                # front controller
│   ├── .htaccess                # rewrite para index.php
│   ├── teste_conexBD.php        # smoke test de conexão
│   └── teste_usuarios.html      # ferramenta manual para testar GET /usuarios/{id}
├── swagger.json                 # OpenAPI 2.0 — fonte da verdade da API
├── API_DOCUMENTATION.md         # documentação detalhada dos endpoints
├── API_QUICK_REFERENCE.md       # referência rápida (URLs, exemplos curl)
└── README.md                    # este arquivo
```

## Configuração

### Banco de dados

- Nome do banco: **`cadgas`** (sem o segundo "e" — proposital, alinhado com o `schema.sql`).
- Importar `cadegas/database/schema.sql` no phpMyAdmin (ou via `mysql -u root -p < schema.sql`).
- O script cria o banco, as tabelas e os dados de seed (3 distribuidores, 7 produtos, 1 usuário, 1 pedido).

### Variáveis de ambiente (`.env`)

As credenciais e flags do backend vêm de `cadegas/backend/.env`. O arquivo está no `.gitignore` — cada máquina mantém o seu. Crie-o com base no template abaixo:

```
DB_HOST=localhost
DB_PORT=3306
DB_USER=root
DB_PASSWORD=
DB_NAME=cadgas
APP_DEBUG=1
CORS_ORIGIN=http://localhost
ROUTES_BASE=/cadegas/backend/public
```

Notas:

- **`DB_PASSWORD`**: algumas instalações de UwAmp deixam o root com senha `root` em vez de vazia. Se o smoke test de conexão falhar, ajuste para `DB_PASSWORD=root`.
- **`APP_DEBUG=1`** mostra mensagens de erro PHP em desenvolvimento. Em produção, use `0`.
- **`ROUTES_BASE`** é o prefixo de URL onde `public/` é servido — só ajuste se fizer deploy em outro caminho.

### Teste de conexão

Abra no navegador:

```
http://localhost/cadegas/backend/public/teste_conexBD.php
```

Resposta esperada (200):

```json
{ "status": "Conectado ao banco com sucesso" }
```

Se vier `{"erro": "Falha ao conectar ao banco"}`, revise o `.env`.

## Endpoints da API

Base URL (dev): `http://localhost/cadegas/backend/public`

### Autenticação

#### `POST /register` — cadastro de usuário

**Body:**

```json
{
  "nome": "Amanda Ribeiro",
  "email": "amanda@email.com",
  "telefone": "31999999999",
  "senha": "123456",
  "endereco": "Rua A, 100",
  "cidade": "Belo Horizonte",
  "estado": "MG",
  "cep": "30000-000"
}
```

`nome`, `email`, `telefone`, `senha` são obrigatórios. Endereço/cidade/estado/cep são opcionais (podem ser informados depois).

**Resposta 201:**

```json
{
  "mensagem": "Usuário cadastrado com sucesso",
  "id_usuario": 1,
  "email": "amanda@email.com"
}
```

#### `POST /login` — login do usuário

**Body:**

```json
{ "email": "amanda@email.com", "senha": "123456" }
```

**Resposta 200:**

```json
{
  "mensagem": "Login realizado com sucesso",
  "id_usuario": 1,
  "nome": "Amanda Ribeiro",
  "email": "amanda@email.com",
  "usuario": { "id": 1, "nome": "Amanda Ribeiro", "email": "amanda@email.com" }
}
```

> A chave `usuario` é mantida para compatibilidade com clientes antigos. Clientes novos devem ler `id_usuario`/`nome`/`email` no nível raiz.

### Usuários

#### `GET /usuarios/{id}` — perfil completo do usuário

Usado pelo frontend no checkout para pré-preencher endereço e exibir informações de contato.

**Resposta 200:**

```json
{
  "id_usuario": 1,
  "nome": "Amanda Ribeiro",
  "email": "amanda@email.com",
  "telefone": "31999999999",
  "endereco": "Rua A, 100",
  "cidade": "Belo Horizonte",
  "estado": "MG",
  "cep": "30000-000"
}
```

Campos nulos no banco vêm como string vazia (`""`).

Erros: `400` (ID inválido), `404` (não encontrado).

### Distribuidores

#### `GET /distribuidores` — lista todos os distribuidores ativos

**Resposta 200** (array direto):

```json
[
  {
    "id_distribuidor": 1,
    "nome_empresa": "Gás Central",
    "cnpj": "12.345.678/0001-90",
    "telefone": "3133333333",
    "endereco": "Rua A, 123",
    "cidade": "Belo Horizonte",
    "estado": "MG",
    "taxa_entrega": 10.0,
    "ativo": 1
  }
]
```

#### `GET /distribuidores/{id}/produtos` — produtos de um distribuidor

**Resposta 200:**

```json
{
  "distribuidor_id": "1",
  "produtos": [
    {
      "id_produto": 1,
      "id_distribuidor": 1,
      "nome": "Botijão P13",
      "descricao": "Botijão residencial 13kg",
      "preco": 95.0,
      "disponivel": 1
    }
  ]
}
```

### Produtos

#### `GET /produtos` — lista geral de produtos disponíveis

Inclui `nome_empresa` e `taxa_entrega` do distribuidor — usado pela tela inicial pós-login.

**Resposta 200:**

```json
{
  "produtos": [
    {
      "id_produto": 1,
      "id_distribuidor": 1,
      "nome": "Botijão P13",
      "descricao": "Botijão residencial 13kg",
      "preco": 95.0,
      "disponivel": 1,
      "nome_empresa": "Gás Central",
      "taxa_entrega": 10.0
    }
  ]
}
```

### Pedidos

#### `POST /pedidos` — criar pedido

**Body:**

```json
{
  "id_usuario": 1,
  "id_distribuidor": 1,
  "itens": [
    { "id_produto": 1, "quantidade": 2 }
  ],
  "forma_pagamento": "pix",
  "endereco_entrega": "Rua A, 100, Belo Horizonte/MG — CEP 30000-000"
}
```

- `forma_pagamento`: opcional, um de `dinheiro` | `pix` | `cartao` (default `dinheiro`).
- `endereco_entrega`: opcional. Se ausente, o backend usa o endereço cadastrado do usuário.

**Resposta 201:**

```json
{
  "mensagem": "Pedido criado com sucesso",
  "id_pedido": 1,
  "subtotal": 190.0,
  "taxa_entrega": 10.0,
  "total": 200.0,
  "forma_pagamento": "pix",
  "status": "pendente"
}
```

Pedido + itens são gravados em uma transação atômica.

#### `GET /pedidos/{id}` — consultar pedido

**Resposta 200:**

```json
{
  "pedido": {
    "id_pedido": 1,
    "id_usuario": 1,
    "id_distribuidor": 1,
    "status": "pendente",
    "subtotal": 190.0,
    "taxa_entrega": 10.0,
    "total": 200.0,
    "forma_pagamento": "pix",
    "endereco_entrega": "Rua A, 100, Belo Horizonte/MG — CEP 30000-000",
    "criado_em": "2026-05-12 10:00:00"
  },
  "itens": [
    {
      "id_produto": 1,
      "nome": "Botijão P13",
      "descricao": "Botijão residencial 13kg",
      "quantidade": 2,
      "preco_unitario": 95.0,
      "subtotal": 190.0
    }
  ],
  "mensagem": "O distribuidor entrará em contato para confirmar a entrega"
}
```

## Formato de erro

Todos os endpoints devolvem erro no mesmo formato:

```json
{ "erro": "mensagem descritiva" }
```

Status codes em uso: `200`, `201`, `400`, `401`, `404`, `409`, `500`.

## Documentação complementar

- **`API_DOCUMENTATION.md`** — detalhes de cada endpoint, schemas, exemplos JS.
- **`API_QUICK_REFERENCE.md`** — cheatsheet com URLs e exemplos curl.
- **`swagger.json`** — OpenAPI 2.0 (fonte da verdade da API).
