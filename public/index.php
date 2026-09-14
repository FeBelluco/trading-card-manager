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
    <meta name="csrf-token" content="<?= escape(csrf_token()) ?>">
</head>
<body class="catalog-page">
    <main class="portal-panel">
<header class="portal-header">
    <div class="portal-brand">
        <svg class="brand-icon" viewBox="0 0 48 48"
            fill="none" aria-hidden="true">
            <rect x="7" y="5" width="25" height="34" rx="4"
                stroke="currentColor" stroke-width="2"
                transform="rotate(-12 7 5)" />
            <rect x="15" y="9" width="25" height="34" rx="4"
                fill="currentColor" />
        </svg>

        <div>
            <h1>Gerenciador de Cartas</h1>
            <p>Painel administrativo</p>
        </div>
    </div>

    <div class="account-actions">
        <span>Olá, <?= escape($_SESSION['username']) ?>.</span>

        <form class="logout-form" method="post" action="/logout.php">
            <input type="hidden" name="csrf_token"
                value="<?= escape(csrf_token()) ?>">
            <button type="submit">Sair</button>
        </form>
    </div>
</header>
        <section aria-labelledby="cards-heading">
<div class="list-header">
    <div>
        <h2 id="cards-heading">Cartas cadastradas</h2>
        <p>Gerencie seu catálogo de Magic, Pokémon e Yu-Gi-Oh!</p>
    </div>

    <a class="button-link" href="/card-new.php">
        <span aria-hidden="true">＋</span> Cadastrar carta
    </a>
</div>
            <?php
            $successMessage = '';

            if (($_GET['created'] ?? '') === '1') {
                $successMessage = 'Carta cadastrada com sucesso.';
            } elseif (($_GET['updated'] ?? '') === '1') {
                $successMessage = 'Carta atualizada com sucesso.';
            }
            ?>
            <p id="cards-action-status" class="success-notice" role="status"><?= escape($successMessage) ?></p>
            <p id="cards-status" role="status">Carregando cartas…</p>
            <button id="cards-retry" type="button" hidden>Tentar novamente</button>
            <a id="cards-login" href="/login.php" hidden>Entrar novamente</a>
            <noscript><p>Ative o JavaScript para visualizar a lista de cartas.</p></noscript>
            <div id="cards-region" class="table-scroll" aria-busy="true"
                role="region" aria-label="Lista de cartas" tabindex="0">
                <table id="cards-table" class="cards-table" hidden>
                    <caption>Cartas cadastradas, em ordem de nome em inglês</caption>
                    <thead><tr>
                        <th scope="col">Imagem</th>
                        <th scope="col">Nome em inglês</th>
                        <th scope="col">Nome em português</th>
                        <th scope="col">Card Game</th>
                        <th scope="col">Edição</th>
                        <th scope="col">Raridade</th>
                        <th scope="col">Ações</th>
                    </tr></thead>
                    <tbody id="cards-rows"></tbody>
                </table>
            </div>
        </section>
        <dialog id="delete-dialog" aria-labelledby="delete-title" aria-describedby="delete-description">
            <h2 id="delete-title">Excluir carta?</h2>
            <p id="delete-description"></p>
            <p>Esta ação remove a carta e sua imagem e não pode ser desfeita.</p>
            <p id="delete-status" role="status"></p>
            <a id="delete-login" href="/login.php" hidden>Entrar novamente</a>
            <div class="dialog-actions">
                <button id="delete-cancel" type="button" autofocus>Cancelar</button>
                <button id="delete-confirm" class="danger" type="button">Excluir carta</button>
            </div>
        </dialog>
        <dialog id="image-dialog" aria-labelledby="image-dialog-title">
            <div class="image-dialog-header">
                <h2 id="image-dialog-title">Imagem da carta</h2>
                <form method="dialog">
                    <button class="image-dialog-close"
                        type="submit"
                        aria-label="Fechar imagem"
                        autofocus>
                        <svg width="20" height="20" viewBox="0 0 24 24"
                            fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" aria-hidden="true">
                            <path d="M6 6L18 18M18 6L6 18" />
                        </svg>
                    </button>
                </form>
            </div>
            <img id="image-dialog-photo" alt="">
        </dialog>
    </main>
</body>
</html>
