# Gerenciador de Cartas

Desafio técnico Full Stack com foco em Front-end. PHP sem frameworks, MySQL,
HTML5, CSS3 e JavaScript Vanilla.

## Estado atual

Ambiente, conexão PDO, schema, dados iniciais e autenticação implementados.
Login, logout, listagem, cadastro, edição e exclusão de cartas com imagens
funcionam. A revisão final da interface e da entrega permanece pendente.
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
os volumes do banco e das imagens. Evite adicionar `-v`, pois essa opção exclui os dados.

## Organização

```text
public/       conteúdo acessível pelo navegador
src/          código PHP da aplicação
scripts/      verificações e futuros comandos de inicialização
database/     schema SQL e dados iniciais dos jogos e edições
docker/       configuração de Apache e PHP
docs/         checklist dos requisitos
storage/      ponto de montagem do volume de imagens (fora da pasta pública)
```

## Próximas etapas

1. Revisão da interface e testes finais dos fluxos completos.
2. Testar instalação do zero e conferir o checklist da entrega.
3. Liberar acesso aos avaliadores no GitHub e enviar o link do repositório.

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
- Confirmar exclusão com o nome da carta e foco inicial em Cancelar: reduz o
  risco de remover o registro errado e oferece uma saída imediata pelo teclado.
- Na edição, manter a imagem quando nenhuma nova é enviada: evita exigir que
  o usuário procure o arquivo novamente para corrigir apenas um texto.

## Listagem de cartas

Após login, a página consulta `GET /api/cards.php` usando fetch. O endpoint
retorna `{"cards": [...]}` com nomes, raridade, jogo e edição; os relacionamentos
são consultados via JOIN. A ordem é nome em inglês e ID como desempate.
Sem sessão, retorna JSON com HTTP 401. O endpoint também aceita POST para
cadastro; outros métodos retornam HTTP 405. A consulta GET não exige CSRF
porque não altera dados; o cadastro exige o token.

`src/cards.php` contém a consulta SQL; `public/api/cards.php` trata HTTP e
autenticação; `public/assets/cards.js` cria as linhas pelo DOM, com textContent.
Há estados de carregamento, lista vazia, erro com nova tentativa e sessão expirada.
A listagem mostra miniaturas por `/image.php?id=ID`, com autenticação, e ainda
não tem paginação. A consulta carrega todas as cartas, adequada à massa pequena
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
com DOM simulado em Node. Na etapa de cadastro, o fluxo com miniaturas também
foi verificado em navegador real e em viewport de 390 pixels.

## Cadastro, edições e imagens

Na lista, clique em Cadastrar carta. A página `/card-new.php` usa HTML, CSS e
o módulo nativo `public/assets/card-form.js`. Não há biblioteca no frontend.

O campo Edição começa desabilitado. Selecionar um jogo faz
`GET /api/editions.php?game=magic` (ou pokemon/yugioh), exibe loading e carrega
as opções do banco. Trocar o jogo limpa a edição anterior e cancela a requisição
pendente com AbortController; respostas antigas também são ignoradas. Erros
permitem nova tentativa e sessão expirada oferece retorno ao login.

O cadastro envia FormData para `POST /api/cards.php`, com token CSRF. O PHP
valida obrigatoriedade e comprimento dos campos e confirma que a edição
pertence ao jogo. Raridade é texto obrigatório; português vazio vira NULL.
Erros de validação retornam HTTP 422 com mensagens por campo. O formulário
preserva texto e arquivo selecionado após erro, destaca o primeiro campo
inválido e bloqueia novos envios enquanto aguarda resposta. Após sucesso
(HTTP 201), volta à lista com confirmação.

Decisões de imagem (o PDF exige imagem, mas não define estes detalhes):

- Uma imagem por carta, JPEG/PNG/WebP, no máximo 5 MiB (5.242.880 bytes;
  apresentado como 5 MB na interface).
- Prévia local antes do envio para conferir o arquivo selecionado.
- Validação no PHP do upload, tamanho real, MIME por fileinfo e identificação
  de imagem por getimagesize; nome original e MIME enviados pelo cliente não
  determinam o formato salvo. SVG não é aceito.
- Nome aleatório gerado pelo servidor. Somente o nome do arquivo vai ao banco.
- Arquivos em `storage/uploads`, no volume Docker `uploads_data`, fora de
  `public/`. O endpoint de imagem exige sessão e não aceita caminhos do cliente.
- Se a gravação no banco falhar, o arquivo recém-enviado é removido. Não existe
  transação atômica entre banco e arquivos; uma interrupção abrupta ainda pode
  deixar um arquivo órfão.
- Sem recorte, redimensionamento ou reprocessamento da imagem nesta etapa.

Para aplicar a configuração do volume em uma instalação anterior:

```sh
docker compose up --build -d --wait
```

As imagens persistem ao recriar o container, assim como os registros no banco.
Um backup completo precisa incluir ambos os volumes. Uploads não entram no Git.

Para revisar, cadastre uma carta deixando português vazio, troque o jogo e
confira o reset das edições, selecione uma foto para ver a prévia e envie.
A carta deve aparecer na lista com sua miniatura. Teste também um arquivo
inválido e uma imagem acima do limite. No Network do navegador, acompanhe
as chamadas das edições e o POST de cadastro.

Verificado em 10/09/2026: três listas de edições, autenticação, CSRF, campos
inválidos, vínculo jogo/edição, arquivos falsos/SVG, limites de upload e corpo
HTTP, JPEG/PNG/WebP, imagem autenticada e rejeição de caminho arbitrário.
No Edge automatizado: troca rápida de jogo, loading, reset, falha e nova
tentativa, preservação de formulário, cadastro com redirecionamento, miniatura,
texto seguro e viewport móvel. Ferramentas de teste foram usadas fora do
projeto; não são dependências de execução. Registros e imagens de teste removidos.

## Edição e exclusão

Na listagem, cada carta possui ações Editar e Excluir.

`/card-edit.php?id=ID` consulta a carta no servidor e reutiliza o formulário
em `src/views/card-form.php`, também usado por `/card-new.php`. Todos os campos
vêm preenchidos. O JavaScript carrega as edições por fetch antes de restaurar
a edição salva. Trocar o jogo continua limpando a seleção. A imagem atual é
mostrada e o envio de uma nova é opcional.

O formulário usa `POST /api/card.php?id=ID`, com `action=update` e token CSRF.
POST mantém o tratamento nativo de multipart/arquivos no PHP. O servidor aplica
as mesmas validações do cadastro. Sem arquivo, preserva a imagem; com arquivo,
salva o novo, confirma o UPDATE e só então remove o antigo. Se o UPDATE falhar,
reverte a transação e remove o novo arquivo.

Excluir abre um dialog nativo com o nome da carta, Cancelar e Excluir carta.
Cancelar ou Escape não faz requisição. Confirmar envia POST ao mesmo endpoint,
com `action=delete` e CSRF. O banco remove o registro antes da remoção do arquivo.
A lista é recarregada após sucesso; falhas permitem tentar novamente.

As duas operações usam transação e SELECT FOR UPDATE para serializar alterações
sobre a mesma carta. IDs inválidos, sessão ausente, CSRF inválido e cartas que
já foram excluídas são tratados. Conflitos de conteúdo não são detectados:
em duas edições válidas, a última gravação prevalece.

O banco e o sistema de arquivos não compartilham transação. Uma falha de limpeza
após commit é registrada no log, sem desfazer a alteração já confirmada; nesse
caso pode restar um arquivo órfão. Nomes de arquivo são verificados antes da remoção.

Para revisar:

1. Edite um texto sem selecionar imagem: a imagem deve continuar a mesma.
2. Troque o jogo e selecione uma nova edição; salve e confira a lista.
3. Envie uma nova imagem: a miniatura deve mudar.
4. Abra Excluir e cancele: a carta deve permanecer.
5. Confirme a exclusão: carta e imagem devem ser removidas.

Verificações em 10/09/2026: API (autenticação, CSRF, método, ID, campos, edição
incompatível, arquivo inválido e grande), mantendo dados anteriores nas falhas.
No Edge: formulário preenchido, edição sem trocar imagem, troca de jogo/imagem,
texto literal, confirmação/cancelamento por botão e Escape, falha com nova
tentativa, exclusão e remoção física das imagens. Recursos excluídos retornam
404 e os registros temporários dos testes foram removidos. Dialog revisado
em viewport de 390 pixels.

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
duplica os dados iniciais. Não há cartas de exemplo no seed: use o formulário
para cadastrar cartas com suas imagens.

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
é um arquivo, com somente seu nome armazenado no banco.

Validação em 09/09/2026: inicialização repetida sem duplicação, hash de senha
verificado, nome português nulo aceito e rejeição de edição inexistente,
exclusão de edição em uso, nome inglês vazio e login duplicado.
Os dados temporários dessas verificações foram revertidos por rollback.

## Referências do ambiente

- [Imagem oficial PHP](https://hub.docker.com/_/php)
- [Imagem oficial MySQL](https://hub.docker.com/_/mysql)
