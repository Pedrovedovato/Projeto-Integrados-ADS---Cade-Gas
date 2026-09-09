# CadêGás — MVP

## Descrição do Projeto
O CadêGás é uma aplicação web desenvolvida com o objetivo de validar uma proposta simples: permitir que consumidores encontrem rapidamente distribuidores disponíveis e realizem pedidos de gás ou água, especialmente fora do horário comercial.
Este projeto foi construído como um MVP (Minimum Viable Product), focado em validar a ideia com o menor nível de complexidade possível.

## Objetivos do MVP
Validar se o usuário consegue:

•	Encontrar distribuidores disponíveis (dentro ou fora do horário comercial) 

•	Visualizar produtos e preços 

•	Realizar um pedido simples 

•	Realizar o pagamento na entrega

O sistema não busca escalabilidade nem automação completa nesta fase.

## Público-Alvo
Consumidor

•	Consumidores residenciais 

•	Consumidor comercial de pequenos comércios 

Distribuidor

•	Distribuidores (participação limitada e manual) 

## Funcionalidades Implementadas (P0)
Usuário

•	Cadastro de consumidor. Exibição de:

o	Nome 

o	Telefone

o	E-mail

o	Endereço (manual) 

•	Login 

## Localização
•	Endereço informado manualmente 

## Distribuidores
•	Listagem de distribuidores. Exibição de: 

o	Nome 

o	Endereço aproximado

o	Produtos disponíveis (gás e/ou água) 

o	Preço do produto

o	Taxa de entrega fixa

## Pedido

•	Seleção de produto (gás ou água) e sua quantidade

•	Carrinho de compras 

•	Resumo do pedido 

## Pagamento
•	Seleção de pagamento offline (Pagamento na entrega): 

o	Dinheiro 

o	Pix 

o	Cartão 

## Finalização
•	Confirmação do pedido 

•	Número do pedido 

•	Mensagem informativa 

## Histórico de Compras
•	Consulta de pedidos anteriores do consumidor (data, distribuidor, itens, forma de pagamento, valores e status)

## Funcionalidades Fora do MVP
Para manter o foco e reduzir complexidade, não foram implementadas:

•	Pagamento online 

•	Rastreamento em tempo real 

•	Notificações (push, SMS, WhatsApp) 

•	Avaliações e comentários 

•	Aplicativo mobile 

•	Gestão completa da operação do distribuidor 

•	Filtros e comparações complexas

## Tecnologias Utilizadas
•	Backend: PHP 

•	Banco de Dados: MySQL 

•	Frontend: HTML, CSS, JavaScript 

•	Ambiente: Docker / UwAmp (Apache + MySQL + PHP)

## Como rodar

### Opção 1: Via Docker (Recomendado)

Pré-requisitos: **Docker** e **Docker Compose** instalados.

1. **Iniciar os containers:**
   ```bash
   docker compose up -d
   ```
   *O container do banco de dados executará automaticamente o script `cadegas/database/schema.sql` na primeira inicialização.*

2. **Acessar a aplicação:**
   - **Frontend:** [http://localhost:8080/](http://localhost:8080/) (redireciona para `cadegas/frontend/pages/welcome.php`)
   - **Backend API:** [http://localhost:8080/cadegas/backend/public/distribuidores](http://localhost:8080/cadegas/backend/public/distribuidores)
   - **Smoke test de conexão:** [http://localhost:8080/cadegas/backend/public/teste_conexBD.php](http://localhost:8080/cadegas/backend/public/teste_conexBD.php)

3. **Parar os containers:**
   ```bash
   docker compose down
   ```

---

### Opção 2: Via UwAmp / XAMPP Local

Pré-requisitos: **UwAmp** (Apache + MySQL + PHP 7+) instalado no Windows e um navegador.

1. **Servir o projeto.** Coloque (ou crie um symlink de) a pasta `cadegas/` deste repo dentro de `www/cadegas/` do UwAmp, de modo que o app fique acessível em `http://localhost/cadegas/...`.
2. **Importar o banco.** No phpMyAdmin (`http://localhost/phpmyadmin`), execute o conteúdo de `cadegas/database/schema.sql`. O script cria o banco `cadgas` (sem o segundo "e", proposital), as tabelas, índices e dados de seed.
3. **Configurar o `.env` do backend.** Crie `cadegas/backend/.env` com base no `cadegas/backend/.env.example`.
4. **Smoke tests** (em ordem):
   - `http://localhost/cadegas/backend/public/teste_conexBD.php` → deve responder `{"status": "Conectado ao banco com sucesso"}`.
   - `http://localhost/cadegas/backend/public/distribuidores` → deve responder com a lista de distribuidores do seed.
5. **Abrir a aplicação:** `http://localhost/cadegas/frontend/` (a raiz redireciona para `pages/welcome.php`).

Fluxo nominal do consumidor: `welcome → register` (criar conta) → `login` → `home` (lista de produtos) → adicionar ao carrinho → `cart` → `checkout` (3 blocos: resumo, contato, endereço pré-preenchido) → confirmação inline → `history` (histórico de pedidos).

> O usuário de seed do `schema.sql` tem hash de senha placeholder e **não consegue logar**. Crie um novo usuário pelo `register.php` para testar.

Documentação detalhada: [backend](cadegas/backend/README.md), [frontend](cadegas/frontend/README.md), [banco](cadegas/database/README.md). API completa em [`cadegas/backend/API_DOCUMENTATION.md`](cadegas/backend/API_DOCUMENTATION.md) ou no `swagger.json`.
