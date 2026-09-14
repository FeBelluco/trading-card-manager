# Verificação de entrega — 10/09/2026

## Base avaliada

Commit `9d1060e`, clonado do GitHub em diretório temporário. Projeto Compose
separado, `liga-review-20260910`, porta 8081 e volumes novos de MySQL e uploads.
O banco e os arquivos do ambiente de desenvolvimento na porta 8080 não foram
usados como base para a instalação limpa.

## Resultado

| Verificação | Resultado |
| --- | --- |
| Build e inicialização do Compose | Sucesso |
| Extensões PHP e conexão PDO | Sucesso |
| Schema, jogos, edições e usuário de teste | Sucesso |
| Segunda execução do inicializador | Sem duplicação |
| Login pelo teclado e página protegida | Sucesso |
| Cadastro com imagem pelo navegador | Sucesso |
| Registro e imagem após reiniciar containers | Preservados |
| Edição com campos e edição restaurados | Sucesso |
| Exclusão, cancelamento e limpeza da imagem | Sucesso |
| Logout e bloqueio da API sem sessão | Sucesso |
| Arquivos internos via URL | HTTP 404 |
| Formulário em 320, 390 e 1280 pixels | Sem overflow horizontal |

Ferramenta de navegador: Playwright com Edge instalado, executada fora do
repositório. A aplicação não depende de Playwright, Node ou pacotes npm.
A tabela usa rolagem horizontal local para manter as colunas legíveis em telas pequenas.

Na primeira automação de reinício, o teste presumiu que reiniciar o container
encerraria a sessão. A sessão permaneceu válida e o login redirecionou corretamente
ao portal. A verificação foi concluída com uma sessão nova, confirmando persistência
e novo login. Nenhuma alteração no código foi necessária por esse motivo.

## Cobertura acumulada das etapas

Também foram verificados: senha incorreta, CSRF ausente/inválido, método HTTP
incorreto, ID inválido/inexistente, vínculo edição/jogo, texto opcional, nomes
longos/vazios, upload falso/SVG/grande, JPEG/PNG/WebP, troca rápida de jogo,
respostas atrasadas, nova tentativa após erro, formulário preservado, troca de
imagem com remoção do arquivo anterior e tratamento de carta já excluída.

## Limites e pendências

- Esta é uma verificação funcional, não uma auditoria de segurança completa.
- As limitações de paginação, tentativas de login e atomicidade banco/arquivos
  estão registradas no README.
- Conferir o acesso dos avaliadores ao repositório privado e enviar o link.
- A revisão pessoal do código e as melhorias opcionais serão feitas depois.
# Revisão da listagem — 14/09/2026

- Sintaxe de `public/index.php`, `src/cards.php` e `public/assets/cards.js`: válida.
- Verificador de ambiente: extensões PHP e conexão MySQL disponíveis.
- Edge automatizado: login, resposta da API e correspondência de `rarity_label`
  com a coluna da tabela nas duas cartas existentes.
- Cabeçalho com Sair à direita em desktop, estilo do link Cadastrar carta e
  navegação para o cadastro verificados.
- Sem transbordamento horizontal da página nas larguras 320, 390 e 1280 pixels.
- Logout seguido de HTTP 401 na API e ausência de erros JavaScript verificados.
- Nenhuma carta ou imagem alterada. CRUD completo e instalação limpa não foram
  repetidos nesta revisão; os resultados anteriores estão registrados abaixo.
