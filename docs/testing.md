# Testes e verificação

## Ambiente

- PHP 8.4 com `pdo_mysql`, `fileinfo` e `session`.
- MySQL 8.4 acessado pelo usuário da aplicação.
- Apache servindo somente a pasta `public/`.
- Banco e uploads mantidos em volumes Docker separados.

O ambiente pode ser verificado com:

```sh
docker compose exec -T app php scripts/check-environment.php
```

## Fluxos verificados

- Login correto e incorreto, renovação de sessão e logout.
- Rejeição de sessão ausente, método HTTP incorreto e token CSRF inválido.
- Listagem vazia, carregamento, falha, nova tentativa e sessão expirada.
- Cadastro com nome em português opcional e vínculo válido entre jogo e edição.
- Carregamento das edições de Magic, Pokémon e Yu-Gi-Oh! por `fetch`.
- Cancelamento de requisições antigas ao trocar rapidamente o jogo.
- Upload e leitura autenticada de JPEG, PNG e WebP.
- Rejeição de arquivo inválido, SVG e imagem acima do limite.
- Edição sem trocar imagem e edição com substituição da imagem existente.
- Cancelamento e confirmação da exclusão pelo mouse e teclado.
- Ampliação da miniatura em modal e fechamento pelo botão ou tecla Escape.
- Layout conferido em larguras de 320, 390 e 1280 pixels.
- Inicialização repetida do banco sem duplicar jogos, edições ou usuário inicial.

Os testes de navegador usados durante o desenvolvimento foram executados fora do projeto e não são dependências da aplicação.

## Limites conhecidos

- O sistema de arquivos não participa da transação SQL. O código remove o novo arquivo quando a gravação falha e remove o antigo somente depois do commit, mas uma interrupção abrupta ainda pode deixar arquivo órfão.
- Duas edições concorrentes válidas não possuem controle otimista de versão; a última gravação prevalece.
- A aplicação não possui rate limiting para tentativas de login.
- A listagem ainda não possui paginação.
