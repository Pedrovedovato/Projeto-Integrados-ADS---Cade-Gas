# 🖼️ Imagens do Projeto CadêGás

## Imagens Disponíveis

### 1. Botijão de Gás
- **Arquivo:** `botijao_wide.jpg`
- **Tamanho:** 39 KB
- **Uso:** Produtos de gás (P13, P45, etc.)
- **Posicionamento:** center 20% (mostra os cilindros azuis)

### 2. Bombona de Água
- **Arquivo:** `imgAgua20L1.jpg`
- **Tamanho:** 115 KB
- **Uso:** Produtos de água (galões 10L, 20L, etc.)
- **Posicionamento:** center 20%

## Configuração no Código

As imagens são usadas dinamicamente no arquivo `products.php` baseado na categoria do produto:

```javascript
// Produtos de gás
produto.category === 'gas' 
    → '../assets/img/botijao_wide.jpg'

// Produtos de água
produto.category === 'water' 
    → '../assets/img/imgAgua20L1.jpg'
```

## Estilo CSS

As imagens são exibidas com os seguintes estilos:

```css
.product-image {
    width: 100%;
    height: 200px;
    background: #f5f5f5;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center 20%;
}
```

## Como Adicionar Novas Imagens

1. Adicione a imagem nesta pasta
2. Atualize `products.php` para usar a nova imagem
3. Ajuste `object-position` no CSS se necessário
4. Documente aqui a nova imagem

## Formatos Suportados

- ✅ JPG/JPEG
- ✅ PNG
- ✅ WebP (browsers modernos)
- ✅ SVG (vetorial)

## Dicas de Otimização

- Comprimir imagens antes de adicionar
- Usar resolução adequada (~800px largura)
- Considerar WebP para melhor compressão
- Manter tamanho de arquivo < 200KB

---

**Última atualização:** 27/04/2026
