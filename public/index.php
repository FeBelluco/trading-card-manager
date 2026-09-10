<?php

declare(strict_types=1);

require dirname(__DIR__) . '/src/auth.php';
start_session();
require_login();
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gerenciador de Cartas</title>
    <link rel="stylesheet" href="/assets/styles.css">
    <script type="module" src="/assets/cards.js"></script>
</head>
<body>
    <main class="portal-panel">
        <h1>Gerenciador de Cartas</h1>
        <p>Olá, <?= escape($_SESSION['username']) ?>.</p>
        <form class="logout-form" method="post" action="/logout.php">
            <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
            <button type="submit">Sair</button>
        </form>
        <section aria-labelledby="cards-heading">
            <h2 id="cards-heading">Cartas cadastradas</h2>
            <a href="/card-new.php">Cadastrar carta</a>
            <?php if (($_GET['created'] ?? '') === '1'): ?>
                <p role="status">Carta cadastrada com sucesso.</p>
            <?php endif; ?>
            <p id="cards-status" role="status">Carregando cartas…</p>
            <button id="cards-retry" type="button" hidden>Tentar novamente</button>
            <a id="cards-login" href="/login.php" hidden>Entrar novamente</a>
            <noscript><p>Ative o JavaScript para visualizar a lista de cartas.</p></noscript>
            <div id="cards-region" class="table-scroll" aria-busy="true"
                role="region" aria-label="Lista de cartas" tabindex="0">
                <table id="cards-table" hidden>
                    <caption>Cartas cadastradas, em ordem de nome em inglês</caption>
                    <thead><tr>
                        <th scope="col">Imagem</th>
                        <th scope="col">Nome em inglês</th>
                        <th scope="col">Nome em português</th>
                        <th scope="col">Card Game</th>
                        <th scope="col">Edição</th>
                        <th scope="col">Raridade</th>
                    </tr></thead>
                    <tbody id="cards-rows"></tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
