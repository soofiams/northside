# Northside — Loja Online (versão PHP + MySQL)

Esta é a versão dinâmica da loja: base de dados a sério, carrinho por sessão, checkout com código de desconto validado no servidor, encomendas gravadas na base de dados, e email de confirmação real por SMTP.

## 1. Colocar os ficheiros no servidor

Copia toda esta pasta para o teu XAMPP/Laragon:
- **XAMPP**: `htdocs/northside/`
- **Laragon**: `www/northside/`

## 2. Criar a base de dados

**Instalação nova:** importa `database/schema.sql` no phpMyAdmin — cria a base de dados `northside`, todas as tabelas, os produtos, avaliações de exemplo, códigos de desconto e tamanhos de roupa.

**Já tinhas a base de dados de antes?** Em vez de reimportar tudo (o que apagaria as tuas encomendas de teste), corre só o `database/migracao_2.sql` — adiciona as tabelas novas (tamanhos, contactos) sem tocar no resto.

## 3. Configurar `config.php`

Abre `config.php` e ajusta:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'northside');
define('DB_USER', 'root');      // o teu utilizador MySQL
define('DB_PASS', '');          // a tua password MySQL
```

Não precisas de configurar o caminho da loja (`URL_BASE`) — é detetado automaticamente a partir da pasta onde colocaste o projeto, seja qual for o nome que lhe deres.

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

## Novidades desta versão

- **Contactos editáveis**: o email e telemóvel que aparecem no modal "Contactos" vêm da tabela `definicoes` — para os alterar, edita a linha correspondente nessa tabela (via phpMyAdmin, por agora; mais tarde terá um formulário no backoffice):
  ```sql
  UPDATE definicoes SET valor = 'novo@email.com' WHERE chave = 'contacto_email';
  UPDATE definicoes SET valor = '+351 91 234 5678' WHERE chave = 'contacto_telefone';
  ```
- **Política de Privacidade**: modal próprio, acessível a partir do rodapé.
- **Tamanhos de roupa**: os produtos Hoodie, Camisola e T-shirt têm agora tamanhos XS a XXL, cada um com o seu próprio stock (tabela `produto_tamanhos`). Um tamanho sem stock aparece riscado e não pode ser escolhido.
- **Avaliação depois da compra**: cada página de produto tem um formulário para deixar uma avaliação (nome, estrelas, comentário). Depois de confirmares uma encomenda, aparecem atalhos diretos para avaliares os produtos que acabaste de comprar.

## Novidades desta versão

- **Devoluções**: página `devolucoes.php` — o cliente introduz o número da encomenda + o email da compra, escolhe os produtos que quer devolver e o motivo. Fica gravado na tabela `devolucoes` (por agora sem um ecrã de gestão — isso entra no backoffice).
- **Chat privado**: um botão flutuante em todas as páginas abre uma conversa entre o cliente e a Northside. Da primeira vez pede nome e email; depois disso, guarda a conversa no browser do cliente (`localStorage`) e mantém o histórico mesmo que ele saia e volte ao site. As respostas da equipa Northside também vão precisar de um ecrã no backoffice — por agora, as mensagens ficam gravadas em `chat_mensagens`, prontas a responder assim que esse ecrã existir.

**Se já tens a base de dados de antes**, corre o `database/migracao_3.sql` no phpMyAdmin (cria só as tabelas novas de devoluções e chat, sem tocar no resto).

## Novidades desta versão

- **Envio grátis corrigido**: havia um erro clássico de vírgula flutuante do PHP — em certos casos, uma soma como 29,99€ + 20,01€ dava internamente algo como 49,999999999996 em vez de 50,00 exato, e por isso o envio grátis não ativava mesmo estando no valor certo. Agora o subtotal é arredondado a 2 casas decimais antes de comparar.
- **Valores de envio editáveis**: o limiar de portes grátis (antes fixo em 50€) e o custo de envio (antes fixo em 4,99€) já não estão no código — vivem na tabela `definicoes`, tal como os contactos. Para os alterar:
  ```sql
  UPDATE definicoes SET valor = '75.00' WHERE chave = 'envio_gratis_acima_de';
  UPDATE definicoes SET valor = '5.99' WHERE chave = 'envio_custo';
  ```
- Sobre a **percentagem** dos descontos: essa já vivia na base de dados desde o início (tabela `codigos_desconto`, coluna `percentagem`) — não está fixa no código. Para criares ou alterares um código:
  ```sql
  UPDATE codigos_desconto SET percentagem = 0.20 WHERE codigo = 'NORTHSIDE10';
  INSERT INTO codigos_desconto (codigo, percentagem, ativo) VALUES ('VERAO25', 0.25, 1);
  ```

**Se já tens a base de dados de antes**, corre o `database/migracao_4.sql` no phpMyAdmin.

## Backoffice (painel de administração)

Acede a `http://localhost/[a-tua-pasta]/admin/setup.php` **uma única vez** para criares a tua conta — depois disso, apaga o ficheiro `admin/setup.php` do servidor por segurança.

Depois, entra em `admin/login.php`. O painel tem:

- **Painel** — estatísticas gerais (encomendas, faturação, produtos esgotados, devoluções e mensagens de chat pendentes)
- **Produtos** — criar, editar, eliminar; upload de imagem; especificações (garantia, material, etc.) editáveis livremente; tamanhos e stock por tamanho para a roupa
- **Encomendas** — ver todos os dados do cliente, os produtos comprados, e mudar o estado (pendente → confirmada → enviada → entregue)
- **Devoluções** — ver os pedidos dos clientes e aprovar/rejeitar
- **Códigos de Desconto** — criar, editar, eliminar, definir percentagem e validade
- **Avaliações** — mostrar/ocultar ou eliminar avaliações
- **Chat** — responder às conversas dos clientes em tempo real (atualiza automaticamente a cada poucos segundos)
- **Definições** — email e telemóvel de contacto, limiar de portes grátis, e custo de envio — os mesmos valores que já eram editáveis por SQL, agora com um formulário simples

## Pagamentos com a Stripe (Apple Pay, Google Pay, MB WAY, Klarna)

O checkout já está ligado à Stripe a sério — falta só a tua parte:

### 1. Criar a conta Stripe
Vai a [dashboard.stripe.com/register](https://dashboard.stripe.com/register) e cria a conta (podes começar em modo de teste, sem verificares o negócio já).

### 2. Instalar a Stripe no projeto
```bash
cd /caminho/para/o/projeto
composer require stripe/stripe-php
```

### 3. Copiar as chaves de teste
Em [dashboard.stripe.com/test/apikeys](https://dashboard.stripe.com/test/apikeys), copia a **Publishable key** e a **Secret key**, e cola-as no `config.php`:
```php
define('STRIPE_CHAVE_PUBLICA', 'pk_test_...');
define('STRIPE_CHAVE_SECRETA', 'sk_test_...');
```

### 4. Ativar os métodos de pagamento
No painel da Stripe: **Definições → Métodos de pagamento** — ativa Cartão, Apple Pay, Google Pay, Klarna e MB WAY (para Portugal).

### 5. Testar
Corre `database/migracao_8.sql` no phpMyAdmin, faz uma compra de teste no site, e usa um [cartão de teste da Stripe](https://docs.stripe.com/testing) (ex: `4242 4242 4242 4242`, qualquer data futura, qualquer CVC) para simulares um pagamento aprovado, sem dinheiro real.

### Quando estiveres pronta para receber pagamentos a sério
Repete o passo 3, mas com as chaves que começam por `pk_live_` e `sk_live_` (têm de ativar a conta Stripe com os dados reais do negócio primeiro).

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
