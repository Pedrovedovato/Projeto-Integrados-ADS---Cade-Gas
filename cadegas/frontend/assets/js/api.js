/**
 * Módulo de comunicação com API
 * Baseado em swagger-1.json - CadêGás API v1.1.0
 *
 * Base Path: /cadegas/backend/public
 */

const API_BASE = '/cadegas/backend/public';

/**
 * Helper para fazer requisições à API
 * @param {string} endpoint - Endpoint relativo ao base path
 * @param {object} options - Opções do fetch
 * @returns {Promise<object>} Response JSON
 */
async function apiRequest(endpoint, options = {}) {
    const url = `${API_BASE}${endpoint}`;

    const config = {
        headers: {
            'Content-Type': 'application/json',
            ...options.headers
        },
        ...options
    };

    try {
        const response = await fetch(url, config);
        const data = await response.json();

        // Swagger usa "erro" para erros, não "error"
        if (data.erro) {
            throw new Error(data.erro);
        }

        // Para responses com array direto (ex: GET /distribuidores)
        if (Array.isArray(data)) {
            return { sucesso: true, dados: data };
        }

        // Para responses com objeto (ex: POST /register)
        return { sucesso: true, ...data };

    } catch (error) {
        return {
            sucesso: false,
            erro: error.message || 'Erro ao conectar com servidor'
        };
    }
}

/**
 * API de Autenticação
 */
const Auth = {
    /**
     * Registrar novo usuário
     * POST /register
     * @param {object} userData - {nome, email, telefone, senha, endereco?, cidade?, estado?, cep?}
     */
    async registrar(userData) {
        return apiRequest('/register', {
            method: 'POST',
            body: JSON.stringify(userData)
        });
    },

    /**
     * Fazer login
     * POST /login
     * @param {string} email
     * @param {string} senha
     * @returns {Promise} {mensagem, id_usuario, nome, email, usuario: {id, nome, email}}
     */
    async login(email, senha) {
        return apiRequest('/login', {
            method: 'POST',
            body: JSON.stringify({ email, senha })
        });
    }
};

/**
 * API de Distribuidores
 */
const Distribuidores = {
    /**
     * Listar distribuidores ativos
     * GET /distribuidores
     * @returns {Promise} Array de DistribuidorResumo
     */
    async listar() {
        return apiRequest('/distribuidores');
    },

    /**
     * Listar produtos de um distribuidor
     * GET /distribuidores/{id}/produtos
     * @param {number} id - ID do distribuidor
     * @returns {Promise} {distribuidor_id, produtos: []}
     */
    async listarProdutos(id) {
        return apiRequest(`/distribuidores/${id}/produtos`);
    }
};

/**
 * API de Produtos
 */
const Produtos = {
    /**
     * Listar todos os produtos disponíveis (tela inicial pós-login)
     * GET /produtos
     * Retorna produtos de todos os distribuidores ativos com disponivel = 1
     * @returns {Promise} {produtos: [ProdutoComDistribuidor]}
     */
    async listarTodos() {
        return apiRequest('/produtos');
    }
};

/**
 * API de Pedidos
 */
const Pedidos = {
    /**
     * Criar novo pedido
     * POST /pedidos
     * @param {object} pedidoData - {id_usuario, id_distribuidor, itens, forma_pagamento?, endereco_entrega?}
     * @returns {Promise} {mensagem, id_pedido, subtotal, taxa_entrega, total, forma_pagamento, status}
     */
    async criar(pedidoData) {
        return apiRequest('/pedidos', {
            method: 'POST',
            body: JSON.stringify(pedidoData)
        });
    },

    /**
     * Buscar detalhes do pedido
     * GET /pedidos/{id}
     * @param {number} id - ID do pedido
     * @returns {Promise} {pedido: {...}, itens: [], mensagem}
     */
    async buscar(id) {
        return apiRequest(`/pedidos/${id}`);
    }
};

// Exportar para uso global
window.API = {
    Auth,
    Distribuidores,
    Produtos,
    Pedidos
};
