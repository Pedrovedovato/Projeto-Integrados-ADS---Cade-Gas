Backend API (MVP)
Backend da API REST do projeto CadêGás, desenvolvido em PHP + MySQL, com foco na validação de um MVP (Produto Mínimo Viável).
O sistema permite:

cadastro e login de usuários
listagem de distribuidores
listagem de produtos
criação e consulta de pedidos


Objetivo do Projeto
Validar a proposta de valor de um sistema que conecta consumidores a distribuidores de gás e água, permitindo pedidos simples e confirmação offline.

O projeto não possui funcionalidades avançadas como pagamento online ou rastreamento em tempo real, pois estas estão fora do escopo do MVP.


Tecnologias Utilizadas

PHP 7+
MySQL 5.7+
PDO
UwAmp
Postman
Arquitetura MVC simples
API REST (JSON)

Estrutura do Projeto
backend/
├── config/
│   └── database.php
│
├── controllers/
│   ├── AuthController.php
│   ├── DistribuidorController.php
│   └── PedidoController.php
│
├── models/
│   ├── Usuario.php
│   ├── Distribuidor.php
│   ├── Produto.php
│   └── Pedido.php
│
├── routes.php
└── public/
    ├── index.php
    └── teste_conexBD.php

Configuração do Ambiente
Requisitos

UwAmp (Apache + MySQL + PHP)
PHP 7+
MySQL 5.7+
Postman


Banco de Dados

Nome do banco: cadgas
Importar o arquivo schema.sql no phpMyAdmin
Credenciais padrão:

usuário: root
senha: root




Conexão com o Banco
Arquivo: config/database.php
$user = 'root';
$pass = 'root';
$dbName = 'cadgas';

Teste de Conexão
http://localhost/backend/public/teste_conexBD.php

Resposta esperada
{
  "status": "Conectado ao banco de dados com sucesso"
}

Endpoints da API
Autenticação
POST /register – Cadastro de usuário
Body:
{
  "nome": "Amanda Ribeiro",
  "email": "amanda@email.com",
  "telefone": "31999999999",
  "senha": "123456"
}

Resposta:
{
  "mensagem": "Usuário cadastrado com sucesso"
}

POST /login – Login do usuário
Body:
{
  "email": "amanda@email.com",
  "senha": "123456"
}

Resposta:
{
  "mensagem": "Login realizado com sucesso",
  "usuario": {
    "id": 1,
    "nome": "Amanda Ribeiro",
    "email": "amanda@email.com"
  }
}

Distribuidores
GET /distribuidores
Retorna distribuidores ativos.
[
  {
    "id_distribuidor": 1,
    "nome_fantasia": "Gás Central",
    "endereco": "Rua A, 123",
    "taxa_entrega": 10
  }
]

GET /distribuidores/{id}/produtos
Retorna produtos de um distribuidor.
{
  "distribuidor_id": "1",
  "produtos": [
    {
      "id_produto": 1,
      "descricao": "Botijão residencial padrão",
      "preco": 95.00
    }
  ]
}

Pedidos
POST /pedidos – Criar pedido
Body:
{
  "id_usuario": 1,
  "id_distribuidor": 1,
  "itens": [
    {
      "id_produto": 1,
      "quantidade": 1
    }
  ]
}
Resposta:
{
  "mensagem": "Pedido criado com sucesso",
  "id_pedido": 1,
  "total": 103.00
}

GET /pedidos/{id} – Consulta de pedido
{
  "pedido": {
    "id_pedido": 1,
    "id_usuario": 1,
    "id_distribuidor": 1,
    "total": 103.00,
    "data": "2026-04-22 14:31:08"
  },
  "itens": [
    {
      "id_produto": "1",
      "descricao": "Botijão residencial padrão",
      "quantidade": "1",
      "preco_unitario": "95.00"
    }
  ],
  "mensagem": "O distribuidor entrará em contato para confirmar a entrega"
}