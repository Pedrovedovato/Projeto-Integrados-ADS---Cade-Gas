// frontend/api-config.js
// Configuração da API para diferentes ambientes

const API_CONFIG = {
  // Ambiente de Desenvolvimento
  development: {
    BASE_URL: 'http://localhost/backend/public',
    TIMEOUT: 30000,
    DEBUG: true
  },

  // Ambiente de Testes/Homologação
  staging: {
    BASE_URL: 'https://api-staging.cadegas.com/public',
    TIMEOUT: 30000,
    DEBUG: true
  },

  // Ambiente de Produção
  production: {
    BASE_URL: 'https://api.cadegas.com/public',
    TIMEOUT: 30000,
    DEBUG: false
  }
};

// Detectar ambiente atual
const CURRENT_ENV = process.env.NODE_ENV || 'development';
const CONFIG = API_CONFIG[CURRENT_ENV];

/**
 * Classe para gerenciar requisições à API
 */
class ApiClient {
  constructor(config) {
    this.baseUrl = config.BASE_URL;
    this.timeout = config.TIMEOUT;
    this.debug = config.DEBUG;
  }

  /**
   * Registrar novo usuário
   */
  async register(nome, email, telefone, senha) {
    return this.post('/register', {
      nome,
      email,
      telefone,
      senha
    });
  }

  /**
   * Fazer login
   */
  async login(email, senha) {
    const response = await this.post('/login', {
      email,
      senha
    });
    
    if (response.id_usuario) {
      this.saveUserId(response.id_usuario);
    }
    
    return response;
  }

  /**
   * Fazer logout
   */
  logout() {
    this.clearUserId();
  }

  /**
   * Listar distribuidores ativos
   */
  async listDistribuidores() {
    return this.get('/distribuidores');
  }

  /**
   * Listar produtos de um distribuidor
   */
  async listProdutos(distribuidorId) {
    return this.get(`/distribuidores/${distribuidorId}/produtos`);
  }

  /**
   * Criar novo pedido
   */
  async criarPedido(distribuidorId, itens) {
    const idUsuario = this.getUserId();
    
    if (!idUsuario) {
      throw new Error('Usuário não autenticado');
    }

    return this.post('/pedidos', {
      id_usuario: parseInt(idUsuario),
      id_distribuidor: distribuidorId,
      itens
    });
  }

  /**
   * Buscar detalhes de um pedido
   */
  async buscarPedido(pedidoId) {
    return this.get(`/pedidos/${pedidoId}`);
  }

  /**
   * Requisição GET genérica
   */
  async get(endpoint) {
    return this.request(endpoint, {
      method: 'GET'
    });
  }

  /**
   * Requisição POST genérica
   */
  async post(endpoint, data) {
    return this.request(endpoint, {
      method: 'POST',
      body: JSON.stringify(data)
    });
  }

  /**
   * Requisição genérica com tratamento de erro
   */
  async request(endpoint, options = {}) {
    const url = `${this.baseUrl}${endpoint}`;
    
    const defaultOptions = {
      headers: {
        'Content-Type': 'application/json'
      },
      ...options
    };

    try {
      if (this.debug) {
        console.log(`🔄 [${defaultOptions.method}] ${url}`, {
          body: options.body ? JSON.parse(options.body) : null
        });
      }

      const controller = new AbortController();
      const timeoutId = setTimeout(() => controller.abort(), this.timeout);

      const response = await fetch(url, {
        ...defaultOptions,
        signal: controller.signal
      });

      clearTimeout(timeoutId);

      const data = await response.json();

      if (!response.ok) {
        if (this.debug) {
          console.error(`❌ Erro ${response.status}:`, data);
        }
        throw new ApiError(data.erro || 'Erro na requisição', response.status, data);
      }

      if (this.debug) {
        console.log(`✅ Sucesso:`, data);
      }

      return data;
    } catch (error) {
      if (error instanceof ApiError) {
        throw error;
      }

      if (error.name === 'AbortError') {
        throw new ApiError('Requisição expirou', 408, null);
      }

      throw new ApiError(error.message, 500, null);
    }
  }

  /**
   * Salvar ID do usuário no localStorage
   */
  saveUserId(id) {
    localStorage.setItem('cadegas_user_id', id);
  }

  /**
   * Recuperar ID do usuário do localStorage
   */
  getUserId() {
    return localStorage.getItem('cadegas_user_id');
  }

  /**
   * Limpar dados do usuário do localStorage
   */
  clearUserId() {
    localStorage.removeItem('cadegas_user_id');
  }

  /**
   * Verificar se usuário está autenticado
   */
  isAuthenticated() {
    return !!this.getUserId();
  }
}

/**
 * Classe customizada para erros da API
 */
class ApiError extends Error {
  constructor(message, status, data) {
    super(message);
    this.name = 'ApiError';
    this.status = status;
    this.data = data;
  }
}

// Exportar instância única
const api = new ApiClient(CONFIG);

// Se for Node.js/CommonJS
if (typeof module !== 'undefined' && module.exports) {
  module.exports = { api, ApiClient, ApiError, API_CONFIG };
}

// Se for ES Modules
if (typeof export !== 'undefined') {
  export { api, ApiClient, ApiError, API_CONFIG };
}
