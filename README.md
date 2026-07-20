# Northside — Loja Online
#PROJETO EM CONCLUSÃO!!!


Loja online completa em **PHP + MySQL**: catálogo de produtos, página de produto com stock, carrinho de compras (adicionar/remover/atualizar) e painel de administração para gerir os produtos.

## O que precisas para pôr a loja a funcionar

- Um servidor com **PHP 8+** e **MySQL/MariaDB** (ex: XAMPP, MAMP, Laragon no teu computador, ou qualquer hosting com cPanel)
- Não precisas de instalar nada extra — não há dependências externas (sem Composer, sem frameworks)

## Passo 1 — Colocar os ficheiros no servidor

Copia toda esta pasta `northside/` para a pasta pública do teu servidor:
- **XAMPP**: `htdocs/northside/`
- **MAMP**: `htdocs/northside/`
- **Laragon**: `www/northside/`
- **Hosting (cPanel)**: `public_html/` (ou uma subpasta, se preferires)

## Passo 2 — Criar a base de dados

1. Abre o **phpMyAdmin** (ou outro gestor MySQL)
2. Cria uma base de dados chamada `northside` (ou importa diretamente — o ficheiro já cria a base de dados)
3. Importa o ficheiro `database/northside.sql`
   - Isto cria as tabelas `produtos`, `categorias`, `admins`
   - E insere as 5 categorias do menu + 6 produtos de exemplo (iguais aos da imagem que enviaste)

## Passo 3 — Configurar a ligação à base de dados

Abre o ficheiro `config.php` e ajusta estas linhas com os dados do teu servidor:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'northside');
define('DB_USER', 'root');      // o teu utilizador MySQL
define('DB_PASS', '');          // a tua password MySQL
```

Se a loja não estiver na raiz do site (ex: `teusite.com/northside/`), ajusta também:
```php
define('URL_BASE', '/northside/');
```

## Passo 4 — Criar a tua conta de administrador

No browser, acede a:
```
http://localhost/northside/admin/setup.php
```
Escolhe um utilizador e password. Depois disso **apaga o ficheiro `admin/setup.php`** por segurança (só é preciso uma vez).

## Passo 5 — Já está! Explora a loja

- **Loja (cliente)**: `http://localhost/northside/index.php`
- **Painel de admin**: `http://localhost/northside/admin/login.php`

## Como funciona cada parte

| Ficheiro/pasta | O que faz |
|---|---|
| `index.php` | Página inicial (hero + destaques), igual ao layout que enviaste |
| `loja.php` | Catálogo completo, com filtro por categoria (menu) e pesquisa |
| `produto.php?id=X` | Página do produto — mostra **"Em stock"**, **"Últimas unidades"** ou **"Esgotado"** consoante a quantidade |
| `carrinho.php` | Carrinho — atualizar quantidade ou remover produtos |
| `actions/carrinho_add.php` | Adiciona ao carrinho (nunca deixa passar do stock disponível) |
| `actions/carrinho_remove.php` | Remove um produto do carrinho |
| `actions/carrinho_update.php` | Atualiza a quantidade de um item no carrinho |
| `admin/` | Painel para criar, editar e eliminar produtos, com upload de imagem e gestão de stock |
| `database/schema.sql` | Estrutura da base de dados + dados de exemplo |

## Gerir produtos (adicionar / remover / alterar stock)

Tudo é feito no painel de administração, sem tocar em código:

1. Faz login em `admin/login.php`
2. Em **Produtos**, podes:
   - **Criar** um novo produto (nome, descrição, preço, categoria, stock, imagem)
   - **Editar** qualquer produto existente
   - **Eliminar** produtos
   - Marcar um produto como **"Destaque"** para aparecer na página inicial
   - Marcar como **"Ativo"**/inativo para o esconder da loja sem apagar

Sempre que alterares o campo **Stock**, isso reflete-se automaticamente:
- Na etiqueta da página do produto (Em stock / Últimas unidades / Esgotado)
- No botão de "Adicionar ao carrinho" (fica desativado se o stock for 0)
- O carrinho nunca deixa um cliente comprar mais unidades do que as que tens em stock

## Próximos passos sugeridos (não incluídos ainda)

- Pagamentos reais (ex: Stripe, MB WAY, Multibanco via SIBS)
- Envio de emails de confirmação de encomenda
- Sistema de contas de cliente (registo/login)
- Página de "Finalizar compra" com moradas de envio
