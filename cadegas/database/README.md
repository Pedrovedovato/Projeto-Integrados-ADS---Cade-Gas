# CadêGás — MVP

Aplicação web que permite ao consumidor encontrar um distribuidor de gás disponível, selecionar um produto e realizar um pedido simples com pagamento na entrega.

> Projeto Integrador — Tecnólogo em Análise e Desenvolvimento de Sistemas  
> SENAC | Disciplina: Desenvolvimento de Sistemas Orientado a Dispositivos Móveis e Baseados na Web

---

## Tecnologias

| Camada         | Tecnologia                        |
|----------------|-----------------------------------|
| Infraestrutura | UwAmp (local)                     |
| Banco de dados | MySQL 5.7+                        |
| Backend        | PHP (CRUD, sessões, validação)    |
| Frontend       | HTML + CSS + JavaScript           |
| Autenticação   | Sessão PHP                        |

---

## Escopo do MVP

O MVP valida se o consumidor consegue encontrar um distribuidor disponível e realizar um pedido simples de gás ou água.

**Fora do escopo:**
- Pagamento online
- Rastreamento em tempo real
- App mobile nativo
- Avaliações e comentários
- Notificações push / SMS

---

## Funcionalidades implementadas (P0)

| Código | User Story                          | Status |
|--------|-------------------------------------|--------|
| US01   | Cadastro de consumidor              | ✅     |
| US02   | Login de consumidor                 | ✅     |
| US03   | Informar localização (endereço)     | ✅     |
| US04   | Visualizar distribuidores ativos    | ✅     |
| US05   | Selecionar produto                  | ✅     |
| US06   | Adicionar produto ao carrinho       | ✅     |
| US07   | Visualizar resumo do pedido         | ✅     |
| US08   | Escolher forma de pagamento         | ✅     |
| US09   | Confirmar pedido                    | ✅     |
| US10   | Ver confirmação do pedido           | ✅     |

---

## Estrutura do repositório

```
cadegaas-mvp/
├── database/
│   ├── schema.sql          # Script de criação do banco
│   └── README.md           # Documentação do banco de dados
├── backend/                # PHP — controllers, models, rotas
├── frontend/               # HTML, CSS, JS
└── README.md               # Este arquivo
```

---

## Equipe

| Integrante | Responsabilidade          |
|------------|---------------------------|
| [Amanda Soares]     | Banco de dados            |
| [Amanda Ribeiro]     | Backend (PHP)             |
| [Stefano]     | Frontend                  |
| [Pedro e Bruno]     | Arquitetura / Integração  |
