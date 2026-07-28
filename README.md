# Northside — Loja Online (versão PHP + MySQL)

Esta é a versão dinâmica da loja: base de dados a sério, carrinho por sessão, checkout com código de desconto validado no servidor, encomendas gravadas na base de dados, e email de confirmação real por SMTP.

## 1. Colocar os ficheiros no servidor

Copia toda esta pasta para o teu XAMPP/Laragon:
- **XAMPP**: `htdocs/northside/`
- **Laragon**: `www/northside/`

## 2. Criar a base de dados

1. Abre o **phpMyAdmin**
2. Importa o ficheiro `database/schema.sql` — cria a base de dados `northside`, todas as tabelas, os 10 produtos, as avaliações de exemplo e 3 códigos de desconto (`NORTHSIDE10`, `BEMVINDO15`, `PORTO20`)

## 3. Configurar `config.php`

Abre `config.php` e ajusta:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'northside');
define('DB_USER', 'root');      // o teu utilizador MySQL
define('DB_PASS', '');          // a tua password MySQL
```

Se a loja não estiver na raiz do site, ajusta também `URL_BASE` (ex: `/northside/`).

## 4. Configurar o envio de email (SMTP)

O envio de email usa o **PHPMailer**, porque a função `mail()` do PHP normalmente não funciona num XAMPP/Laragon sem configuração extra.

### Passo a passo com Gmail (o mais simples):

1. Instala o [Composer](https://getcomposer.org/download/), se ainda não tiveres
2. No terminal, dentro da pasta do projeto:
   ```bash
   composer require phpmailer/phpmailer
   ```
3. Ativa a **verificação em 2 passos** na tua conta Gmail
4. Cria uma **Password de aplicação** em [myaccount.google.com/apppasswords](https://myaccount.google.com/apppasswords)
5. Em `config.php`, preenche:
   ```php
   define('SMTP_HOST', 'smtp.gmail.com');
   define('SMTP_PORT', 587);
   define('SMTP_UTILIZADOR', 'o-teu-email@gmail.com');
   define('SMTP_PASSWORD', 'a-password-de-aplicacao-de-16-letras');
   ```

Se preferires outro serviço (Outlook, um SMTP do teu hosting, Mailtrap para testes, etc.), muda só o `SMTP_HOST` e a porta — o resto funciona da mesma forma.

**Nota:** se não instalares o PHPMailer, o site tenta usar o `mail()` nativo do PHP como recurso de emergência — mas é muito provável que não envie nada num ambiente local sem configuração adicional. Vais ver isso identificado claramente no ecrã de confirmação da encomenda ("não foi possível enviar o email").

## 5. Já está — explora a loja

- **Loja**: `http://localhost/northside/index.php`
- Testa uma compra completa: adiciona produtos ao carrinho, vai a "Finalizar Compra", experimenta um dos códigos de desconto, e confirma — deves receber o email na caixa de entrada que configuraste.

## O que mudou em relação à versão HTML

| Antes (HTML) | Agora (PHP) |
|---|---|
| Produtos escritos em `PRODUTOS` no JavaScript | Produtos na tabela `produtos` da base de dados |
| Carrinho no `localStorage` do browser | Carrinho na sessão do servidor (`$_SESSION`) |
| Checkout simulado, sem gravar nada | Encomenda gravada em `encomendas` + `encomenda_itens`, com o stock a descontar a sério |
| Código de desconto fixo no JS | Código validado no servidor, contra a tabela `codigos_desconto` |
| "Email enviado" era só uma mensagem no ecrã | Email real, enviado por SMTP via PHPMailer |
| Pesquisa/categoria filtradas no browser | Filtradas com uma query SQL no servidor |

## Estrutura

```
config.php                       → ligação à BD + definições da loja/SMTP
includes/functions.php           → todas as funções (produtos, carrinho, encomendas, desconto)
includes/email.php                → construção e envio do email de confirmação
includes/header.php / footer.php  → layout partilhado por todas as páginas
index.php, loja.php, produto.php, carrinho.php, avaliacoes.php
actions/carrinho_add.php          → adicionar ao carrinho
actions/carrinho_remove.php       → remover do carrinho
actions/carrinho_update.php       → atualizar quantidade
actions/aplicar_desconto.php      → valida o código de desconto (chamado via AJAX)
actions/finalizar_compra.php      → processa o checkout completo (AJAX)
actions/newsletter_subscrever.php → grava a subscrição da newsletter
database/schema.sql               → estrutura da base de dados + dados de exemplo
```

## Próximo passo: o backoffice

Como combinámos, o backoffice (login de admin, gestão de produtos/stock, códigos de desconto, e consulta de encomendas) fica para a fase seguinte — agora que a base de dados e o site público já estão a funcionar a sério, torna-se muito mais direto construir os ecrãs de gestão em cima destas mesmas tabelas.
