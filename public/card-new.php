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
    <title>Cadastrar carta — Gerenciador de Cartas</title>
    <link rel="stylesheet" href="/assets/styles.css">
    <script type="module" src="/assets/card-form.js"></script>
</head>
<body>
<main class="portal-panel">
    <a href="/">Voltar à lista</a>
    <h1>Cadastrar carta</h1>
    <p>Todos os campos são obrigatórios, exceto o nome em português.</p>
    <p id="form-status" role="status"></p>
    <a id="form-login" href="/login.php" hidden>Entrar novamente</a>
    <noscript><p>Ative o JavaScript para carregar as edições e cadastrar cartas.</p></noscript>
    <form id="card-form" action="/api/cards.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
        <fieldset id="card-fields">
            <legend>Dados da carta</legend>
            <label for="name_en">Nome em inglês</label>
            <input id="name_en" name="name_en" maxlength="255" required aria-describedby="name_en-error">
            <span id="name_en-error" class="field-error"></span>
            <label for="name_pt">Nome em português (opcional)</label>
            <input id="name_pt" name="name_pt" maxlength="255" aria-describedby="name_pt-error">
            <span id="name_pt-error" class="field-error"></span>
            <label for="card_game_id">Card Game</label>
            <select id="card_game_id" name="card_game_id" required aria-describedby="card_game_id-error">
                <option value="">Selecione um jogo</option>
                <option value="magic">Magic: The Gathering</option>
                <option value="pokemon">Pokémon</option>
                <option value="yugioh">Yu-Gi-Oh!</option>
            </select>
            <span id="card_game_id-error" class="field-error"></span>
            <label for="edition_id">Edição</label>
            <select id="edition_id" name="edition_id" disabled required aria-describedby="edition-status edition_id-error">
                <option value="">Selecione um jogo primeiro</option>
            </select>
            <span id="edition-status" role="status"></span>
            <span id="edition_id-error" class="field-error"></span>
            <button id="editions-retry" type="button" hidden>Tentar carregar edições novamente</button>
            <label for="rarity">Raridade</label>
            <input id="rarity" name="rarity" maxlength="100" required aria-describedby="rarity-error">
            <span id="rarity-error" class="field-error"></span>
            <label for="image">Imagem da carta</label>
            <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp"
                required aria-describedby="image-help image-error">
            <span id="image-help">Uma imagem JPEG, PNG ou WebP, até 5 MB.</span>
            <span id="image-error" class="field-error"></span>
            <img id="image-preview" class="image-preview" alt="Prévia da imagem selecionada" hidden>
        </fieldset>
        <button id="save-card" type="submit" disabled>Cadastrar carta</button>
    </form>
</main>
</body>
</html>
