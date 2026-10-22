# CadêGás — MVP

Aplicação web desenvolvida para conectar consumidores a distribuidores de gás e água, permitindo localizar fornecedores, avaliar produtos e realizar pedidos com pagamento na entrega.

Este repositório reúne a versão integrada do frontend, backend e banco de dados do MVP do CadêGás, resultante do último merge entre os módulos de interface e API.

## Visão geral

O projeto foi concebido como um MVP (Minimum Viable Product) focado em validar a proposta de valor com o menor nível de complexidade possível. A ideia central é permitir que:

- consumidores encontrem distribuidores disponíveis;
- comparem produtos e preços;
- adicionem itens ao carrinho;
- realizem pedidos simples;
- concluam a compra com pagamento na entrega.

## Público-alvo

### Consumidor
- consumidores residenciais;
- consumidores comerciais de pequenos comércios.

### Distribuidor
- distribuidores que desejam participar manualmente da operação.

## Funcionalidades implementadas

### Usuário
- cadastro de consumidor;
- login;
- endereço informado manualmente;
- consulta de perfil.

### Distribuidores
- listagem de distribuidores disponíveis;
- exibição de nome, endereço aproximado, produtos, preço e taxa de entrega.

### Pedido
- seleção de produto e quantidade;
- carrinho de compras;
- resumo do pedido;
- confirmação final.

### Pagamento
- pagamento offline na entrega;
- opções de dinheiro, Pix e cartão.

### Histórico de compras
- consulta de pedidos anteriores do consumidor com dados do distribuidor, itens, forma de pagamento, valores e status.

## Funcionalidades fora do escopo do MVP

Para manter o foco e reduzir a complexidade, não foram implementadas:

- pagamento online;
- rastreamento em tempo real;
- notificações push/SMS/WhatsApp;
- avaliações e comentários;
- aplicativo mobile;
- gestão completa da operação do distribuidor;
- filtros e comparações complexas.

## Arquitetura do projeto

```text
cadegas/
├─ cadegas/
│  ├─ backend/        # API REST em PHP + MySQL
│  ├─ frontend/       # interface web em PHP, HTML, CSS e JS
│  └─ database/       # schema e documentação do banco
├─ README.md
├─ docker-compose.yml
├─ .gitignore
└─ .idea/
```

## Stack tecnológica

- Backend: PHP
- Banco de dados: MySQL
- Frontend: HTML, CSS, JavaScript
- Ambiente: Docker / UwAmp (Apache + MySQL + PHP)

## Como rodar

### Opção 1: via Docker (recomendado)

Pré-requisitos: Docker e Docker Compose instalados.

1. Inicie os containers:

```bash
docker compose up -d
```

2. Acesse a aplicação:

- Frontend: http://localhost:8080/
- Backend API: http://localhost:8080/cadegas/backend/public/distribuidores
- Smoke test da conexão: http://localhost:8080/cadegas/backend/public/teste_conexBD.php

3. Para encerrar os containers:

```bash
docker compose down
```

### Opção 2: via UwAmp / XAMPP local

Pré-requisitos: UwAmp (Apache + MySQL + PHP 7+) e navegador.

1. Servir o projeto. Coloque a pasta `cadegas/` dentro de `www/cadegas/` do UwAmp.
2. Importe o banco em phpMyAdmin executando `cadegas/database/schema.sql`.
3. Configure o arquivo `.env` do backend com base em `cadegas/backend/.env.example`.
4. Faça os smoke tests:
   - http://localhost/cadegas/backend/public/teste_conexBD.php
   - http://localhost/cadegas/backend/public/distribuidores
5. Acesse a aplicação em http://localhost/cadegas/frontend/

Fluxo nominal do consumidor: `welcome -> register -> login -> home -> cart -> checkout -> history`.

> O usuário seed do `schema.sql` tem hash de senha placeholder e não consegue realizar login. Crie um novo usuário pela página de cadastro para testar o fluxo completo.

## Estrutura dos módulos

- [Backend](cadegas/backend/README.md)
- [Frontend](cadegas/frontend/README.md)
- [Banco de Dados](cadegas/database/README.md)
- [Documentação da API](cadegas/backend/API_DOCUMENTATION.md)

## Observações

- A aplicação foi entregue como MVP funcional, com foco em validação de proposta.
- A comunicação frontend-backend é feita via API REST.
- O banco e a API já estão preparados para execução em ambiente local ou com Docker.

## Status do projeto

Projeto em etapa de validação funcional, com integração completa entre frontend, backend e banco de dados, conforme último merge realizado na branch de integração.
