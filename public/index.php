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
</head>
<body>
    <main class="portal-panel">
        <h1>Gerenciador de Cartas</h1>
        <p>Olá, <?= escape($_SESSION['username']) ?>.</p>
        <p>Você está na área administrativa. A gestão de cartas estará disponível em breve.</p>
        <form method="post" action="/logout.php">
            <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
            <button type="submit">Sair</button>
        </form>
    </main>
</body>
</html>
