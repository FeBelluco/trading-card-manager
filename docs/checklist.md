# Checklist do desafio

Fonte: Processo Seletivo - FullStack (Front).pdf, quatro páginas.
O PDF e o email fornecido não informam prazo de entrega.

## Base técnica

- [x] Estrutura inicial em PHP sem frameworks.
- [x] Compose com PHP/Apache e MySQL isolado do banco local.
- [x] Conexão PDO com utf8mb4 e prepared statements nativos.
- [x] Validar build, inicialização, conexão e acesso HTTP.
- [ ] Implementar interface com HTML5, CSS3 e JavaScript Vanilla, sem bibliotecas ou dependências externas.

## Requisitos funcionais — páginas 1 a 3

- [x] Login e senha, com acesso administrativo protegido.
- [x] Listar cartas.
- [x] Incluir cartas.
- [x] Editar cartas.
- [x] Excluir cartas.
- [x] Nome em inglês e nome em português opcional.
- [x] Select de jogo: Magic: The Gathering, Pokémon e Yu-Gi-Oh!.
- [x] Edição inicialmente desabilitada.
- [x] Fetch das edições ao selecionar um jogo, com loading.
- [x] Popular select com os resultados; recarregar e resetar ao trocar o jogo.
- [x] Reproduzir os 15 IDs e nomes de edições fornecidos no JSON do PDF.
- [x] Imagem da carta.
- [x] Raridade da carta.

## Qualidade proposta — escolhas de implementação

- [x] Sessões, hash de senha e proteção CSRF no login/logout e alterações de cartas.
- [x] Validação no servidor, incluindo vínculo entre edição e jogo no cadastro/edição.
- [x] Consultas parametrizadas e renderização segura de textos nos fluxos atuais.
- [x] Upload com limite, validação do conteúdo e nome gerado pelo servidor.
- [x] Tratar loading, erro e lista vazia, inclusive troca rápida de jogo.
- [ ] Interface responsiva e operação por teclado.
- [ ] Verificar fluxos positivos e falhas relevantes.

## Entrega — página 4

- [ ] GitHub público ou acesso para liga-LeonardoWada e cauaneroberta.
- [ ] README completo com inicialização testada do zero.
- [x] Credenciais válidas de login documentadas.
- [x] Pelo menos duas decisões de UX/Produto implementadas e justificadas.
- [ ] Código autoral e sem ferramentas proibidas; política de IA não detalhada no PDF.
- [x] Schema e massa inicial do banco.
- [ ] CRUD e autenticação com todos os endpoints e fluxos sem erros de execução.

## Verificação da primeira etapa — 08/09/2026

- `docker compose config --quiet` e `docker compose up --build -d --wait`: sucesso.
- PHP 8.4.25, extensões pdo_mysql/fileinfo/session disponíveis e SELECT 1 via PDO: sucesso.
- MySQL 8.4.11 inicializado com usuário da aplicação; serviço saudável.
- Sintaxe dos três arquivos PHP e configuração Apache: válidas.
- Página inicial: HTTP 200 com o título esperado.
- Acesso HTTP a src/database.php, compose.yaml, .env.example e script de verificação: HTTP 404.

## Segunda etapa — 09/09/2026

- [x] Tabelas users, card_games, editions e cards com chaves estrangeiras.
- [x] Comando explícito de inicialização, utilizável com volume existente.
- [x] Três jogos e 15 edições do PDF carregados sem duplicação ao repetir.
- [x] Admin de demonstração criado com hash; credenciais documentadas.
- [x] Validar senha com password_verify e rejeitar senha incorreta.
- [x] Validar nome português opcional, edição inexistente, edição em uso,
  nome inglês vazio e login duplicado, revertendo dados de teste.
- Nesta etapa, a criação do usuário ainda não incluía a autenticação.

## Terceira etapa — autenticação

- [x] Formulário HTML e CSS com validação no servidor e saída escapada.
- [x] Login via PDO e password_verify, com renovação do ID de sessão.
- [x] Página inicial protegida, logout POST e CSRF no login/logout.
- [x] Cookies HttpOnly/SameSite, Secure sob HTTPS e respostas sem cache.
- Novos endpoints de cartas deverão aplicar autenticação e CSRF nas alterações.

## Quarta etapa — listagem

- [x] GET /api/cards.php protegido, com JSON e tratamento de erros.
- [x] Consulta com JOIN de edições e jogos e ordenação por nome/ID.
- [x] Renderização com fetch e DOM nativo usando textContent.
- [x] Estados de carregamento, vazio, erro, nova tentativa e sessão expirada.
- [x] Verificar API e consulta com registros temporários revertidos.
- [x] Verificar estados JS com DOM simulado (não substitui revisão visual).
- Nesta etapa, miniaturas e cadastro ainda estavam pendentes.

## Quinta etapa — cadastro e imagens — 10/09/2026

- [x] Formulário de cadastro com edições carregadas por fetch e cancelamento de requisições antigas.
- [x] POST autenticado, token CSRF, validação de campos e jogo/edição.
- [x] Upload JPEG/PNG/WebP até 5 MiB, prévia e nome aleatório.
- [x] Volume persistente fora de public/ e leitura autenticada das imagens.
- [x] Preservar formulário após erro; impedir envio concorrente pelo botão.
- [x] Testar API e navegador real, incluindo troca rápida, nova tentativa e miniatura.
- [x] Remover os registros e arquivos temporários dos testes.
- Nesta etapa, edição e exclusão ainda estavam pendentes.

## Sexta etapa — edição e exclusão — 10/09/2026

- [x] Formulário compartilhado de cadastro/edição com valores preenchidos.
- [x] Restaurar edição salva depois do fetch; resetar ao trocar o jogo.
- [x] Manter imagem se não houver novo upload; remover antiga após substituição.
- [x] Confirmação de exclusão por dialog com nome e foco em Cancelar.
- [x] POST com autenticação, CSRF e validações, transações com bloqueio da carta.
- [x] Tratar carta inexistente ou excluída enquanto o formulário está aberto.
- [x] Testar API e navegador, incluindo arquivos, cancelamento e falha/repetição.
- Revisão final da interface, instalação limpa e entrega continuam pendentes.
