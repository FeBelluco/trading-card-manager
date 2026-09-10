# Gerenciador de Cartas

Desafio técnico Full Stack com foco em Front-end. PHP sem frameworks, MySQL,
HTML5, CSS3 e JavaScript Vanilla.

## Estado atual

Ambiente, conexão PDO, schema, dados iniciais e autenticação implementados.
Login, logout e listagem de cartas funcionam. Cadastro, edição, exclusão e
imagens ainda estão pendentes.
O progresso está em [docs/checklist.md](docs/checklist.md).

Base validada em 08/09/2026: build e inicialização, PHP 8.4.25, MySQL 8.4.11,
consulta via PDO, sintaxe PHP/Apache e resposta HTTP da página provisória.
Também foi verificado que os arquivos de configuração e código interno não
são servidos pelo navegador.

## Requisitos de ambiente

- Docker Desktop em execução com containers Linux.
- Docker Compose v2 (`docker compose`).
- Porta local 8080 disponível, ou `APP_PORT` configurada com outra porta.

PHP e MySQL são executados nos containers; não é necessário instalar PHP,
Node, Composer ou MySQL no computador para executar esta base.
As imagens usam as linhas PHP 8.4 com Apache/Bookworm e MySQL 8.4.
Essas tags recebem atualizações de patch; não estão fixadas por digest.

## Inicialização

Na raiz do projeto:

```sh
docker compose up --build -d --wait
docker compose exec -T app php scripts/check-environment.php
docker compose exec -T app php scripts/init-database.php
```

Acesse http://localhost:8080. Sem sessão, você será direcionado ao login.
O verificador confirma extensões e uma consulta real
ao banco usando o usuário da aplicação.

O primeiro build precisa de internet para baixar as imagens e pode demorar.
Somente a pasta `public/` é servida pelo Apache; configuração e scripts não
ficam acessíveis pelo navegador.

## Configuração local

O Compose fornece valores padrão exclusivos para desenvolvimento local.
Opcionalmente, copie `.env.example` para `.env` e ajuste porta e senhas antes
da primeira inicialização. `.env` não é versionado.

As senhas de banco são credenciais locais de desenvolvimento, diferentes
das credenciais do usuário de teste descritas abaixo.

O MySQL não publica porta no Windows, portanto pode coexistir com o MySQL
local. Os dados ficam no volume `mysql_data`. Alterar as senhas no `.env`
depois de criar o volume não altera automaticamente os usuários já existentes
no banco; as variáveis da imagem inicializam somente um diretório de dados vazio.

## Comandos úteis

```sh
docker compose ps
docker compose logs --tail=50 app db
docker compose exec -T app php -v
docker compose exec -T app php -m
docker compose stop
docker compose start
```

`docker compose down` remove os containers e a rede do projeto, preservando
o volume do banco. Evite adicionar `-v`, pois essa opção exclui os dados.

## Organização

```text
public/       conteúdo acessível pelo navegador
src/          código PHP da aplicação
scripts/      verificações e futuros comandos de inicialização
database/     schema SQL e dados iniciais dos jogos e edições
docker/       configuração de Apache e PHP
docs/         checklist dos requisitos
```

## Próximas etapas

1. Cadastro, edição, exclusão e upload de imagens, protegendo cada novo endpoint.
2. Interface, manipulação do DOM e carregamento das edições com fetch.
3. Verificação final e revisão das decisões de UX.

## Autenticação e verificação manual

Entre com `admin` / `LigaDev2026!`. O formulário envia POST ao PHP, que consulta
o usuário com PDO, verifica o hash e renova o ID de sessão. A página inicial
exige sessão e o logout aceita somente POST com token CSRF. O login também
valida CSRF. Não há JWT ou senha armazenada no navegador.

1. Abra `/` em uma janela privativa: deve redirecionar para `/login.php`.
2. Use senha incorreta: deve mostrar erro e manter o usuário preenchido.
3. Use as credenciais corretas: deve abrir a área administrativa com seu nome.
4. Clique em Sair e tente acessar `/` novamente: deve exigir login.
5. Use Tab para percorrer campos e botão; Enter envia o formulário.

Os cookies usam HttpOnly, SameSite=Lax e Secure quando o acesso ocorre por
HTTPS. Em localhost HTTP, Secure fica desativado. Respostas de autenticação
e da área protegida usam Cache-Control: no-store. Limitação atual: ainda não
há limitação de tentativas de login; o ambiente é local de demonstração.

## Decisões de UX implementadas

- Preservar o usuário após erro, deixando a senha vazia: reduz redigitação e
  evita devolver a senha no HTML.
- Usar rótulos visíveis, autocomplete de credenciais e foco destacado: facilita
  o uso por teclado e com gerenciadores de senhas, sem depender de placeholders.

## Listagem de cartas

Após login, a página consulta `GET /api/cards.php` usando fetch. O endpoint
retorna `{"cards": [...]}` com nomes, raridade, jogo e edição; os relacionamentos
são consultados via JOIN. A ordem é nome em inglês e ID como desempate.
Sem sessão, retorna JSON com HTTP 401. Outros métodos retornam HTTP 405 para
usuários autenticados. Não exige CSRF porque a consulta GET não altera dados.

`src/cards.php` contém a consulta SQL; `public/api/cards.php` trata HTTP e
autenticação; `public/assets/cards.js` cria as linhas pelo DOM, com textContent.
Há estados de carregamento, lista vazia, erro com nova tentativa e sessão expirada.
A listagem ainda não tem paginação nem miniaturas; imagens serão tratadas na
etapa de upload. A consulta carrega todas as cartas, adequada à massa pequena
do desafio, mas exigiria paginação para um catálogo grande.

Para revisar:

1. Entre no portal: com o banco inicial vazio, aparece “Nenhuma carta cadastrada”.
2. No DevTools, em Network, veja a chamada a `/api/cards.php` e o JSON retornado.
3. Bloqueie essa URL no DevTools e recarregue: deve aparecer erro e Tentar novamente.
4. Desbloqueie a URL e tente novamente: a listagem deve se recuperar.
5. Saia por outra aba e repita a consulta: deve aparecer o link para entrar novamente.

Verificações realizadas: API com e sem sessão, método inválido, consulta com
duas cartas temporárias (JOIN, ordenação e português opcional), revertidas por
rollback. Estados do JavaScript, nova tentativa e texto literal foram verificados
com DOM simulado em Node; a revisão visual no navegador permanece manual.

## Banco e carga inicial

Execute `docker compose exec -T app php scripts/init-database.php` após subir
os serviços. O comando cria as tabelas que não existem, insere três jogos e
as 15 edições do JSON do PDF e cria o usuário de desenvolvimento:

- Usuário: `admin`
- Senha inicial: `LigaDev2026!`

A senha é armazenada como hash gerado por `password_hash`, nunca como texto
puro na tabela. Estas são credenciais públicas de demonstração local.
O usuário pode ser usado na tela de login após executar a inicialização.

Reexecutar preserva registros existentes, incluindo a senha do admin, e não
duplica os dados iniciais. Não há cartas de exemplo nesta etapa: o cadastro
com imagem será implementado junto ao CRUD.

O script funciona também com o volume já criado. `CREATE TABLE IF NOT EXISTS`
não atualiza a estrutura de tabelas existentes; futuras alterações de schema
precisarão de SQL específico. A inicialização não é uma transação única:
DDL no MySQL faz commit implícito. Se falhar, corrija a causa e execute novamente.

| Tabela | Responsabilidade |
| --- | --- |
| `users` | Login único, hash da senha e data de criação. |
| `card_games` | Identificadores `magic`, `pokemon` e `yugioh`. |
| `editions` | IDs e nomes do PDF, com chave estrangeira para o jogo. |
| `cards` | Nomes, edição, caminho da imagem, raridade e datas. |

A carta referencia a edição, que identifica o jogo. Não há campo de jogo
duplicado em `cards`. Chaves estrangeiras impedem referências inexistentes e
exclusão de edições em uso. `name_pt` aceita NULL; os demais campos do cadastro
são obrigatórios. A raridade é texto, pois o PDF não define opções. A imagem
será um arquivo, com somente seu caminho armazenado no banco.

Validação em 09/09/2026: inicialização repetida sem duplicação, hash de senha
verificado, nome português nulo aceito e rejeição de edição inexistente,
exclusão de edição em uso, nome inglês vazio e login duplicado.
Os dados temporários dessas verificações foram revertidos por rollback.

## Referências do ambiente

- [Imagem oficial PHP](https://hub.docker.com/_/php)
- [Imagem oficial MySQL](https://hub.docker.com/_/mysql)
