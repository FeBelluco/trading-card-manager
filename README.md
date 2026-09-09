# Gerenciador de Cartas

Desafio técnico Full Stack com foco em Front-end. PHP sem frameworks, MySQL,
HTML5, CSS3 e JavaScript Vanilla.

## Estado atual

Primeira etapa: ambiente de desenvolvimento, conexão PDO e página provisória.
Login, schema, dados iniciais, CRUD e interface final ainda não foram implementados.
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
```

Acesse http://localhost:8080. Nesta etapa aparece somente a página provisória
“Gerenciador de Cartas”. O verificador confirma extensões e uma consulta real
ao banco usando o usuário da aplicação.

O primeiro build precisa de internet para baixar as imagens e pode demorar.
Somente a pasta `public/` é servida pelo Apache; configuração e scripts não
ficam acessíveis pelo navegador.

## Configuração local

O Compose fornece valores padrão exclusivos para desenvolvimento local.
Opcionalmente, copie `.env.example` para `.env` e ajuste porta e senhas antes
da primeira inicialização. `.env` não é versionado.

As senhas de banco são credenciais locais de desenvolvimento, não credenciais
de login no portal. O usuário de login será incluído na etapa de autenticação.

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
docker/       configuração de Apache e PHP
docs/         checklist dos requisitos
```

## Próximas etapas

1. Schema e seed com os jogos e as 15 edições do PDF.
2. Login, sessões e proteção dos endpoints administrativos.
3. CRUD e upload de imagens.
4. Interface, manipulação do DOM e carregamento das edições com fetch.
5. Verificação final e documentação das credenciais e decisões de UX.

As decisões de UX serão documentadas aqui quando forem implementadas.

## Referências do ambiente

- [Imagem oficial PHP](https://hub.docker.com/_/php)
- [Imagem oficial MySQL](https://hub.docker.com/_/mysql)
