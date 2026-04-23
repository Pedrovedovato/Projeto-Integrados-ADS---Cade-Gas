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

## Como rodar localmente

### Pré-requisitos
- [UwAmp](https://www.uwamp.com/) instalado e rodando
- MySQL ativo (porta padrão 3306)

### Passo a passo

1. Clone o repositório:
   ```bash
   git clone https://github.com/seu-usuario/cadegaas-mvp.git
   ```

2. Importe o banco de dados:
   - Acesse `http://localhost/phpmyadmin`
   - Clique em **SQL** e cole o conteúdo de `database/schema.sql`
   - Clique em **Executar**

   Ou via linha de comando:
   ```bash
   mysql -u root -p < database/schema.sql
   ```

3. Mova a pasta do projeto para o diretório `www` do UwAmp:
   ```
   C:\UwAmp\www\cadegaas-mvp\
   ```

4. Acesse no navegador:
   ```
   http://localhost/cadegaas-mvp
   ```

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
| [Nome]     | Banco de dados            |
| [Nome]     | Backend (PHP)             |
| [Nome]     | Frontend                  |
| [Nome]     | Arquitetura / Integração  |
