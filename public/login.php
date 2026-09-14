<?php

declare(strict_types=1);

require dirname(__DIR__) . '/src/auth.php';
start_session();

if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'], true)) {
    header('Allow: GET, POST');
    http_response_code(405);
    exit;
}

if (isset($_SESSION['user_id'])) {
    header('Location: /', true, 303);
    exit;
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = is_string($_POST['username'] ?? null) ? trim($_POST['username']) : '';
    $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';

    if (!valid_csrf()) {
        http_response_code(403);
        $error = 'Sua sessão expirou ou a solicitação é inválida. Tente novamente.';
    } elseif ($username === '' || $password === '' || strlen($username) > 400 || strlen($password) > 4096) {
        http_response_code(422);
        $error = 'Informe um usuário e uma senha válidos.';
    } else {
        try {
            if (attempt_login($username, $password)) {
                header('Location: /', true, 303);
                exit;
            }
            http_response_code(401);
            $error = 'Usuário ou senha incorretos.';
        } catch (Throwable $exception) {
            error_log((string) $exception);
            http_response_code(503);
            $error = 'Não foi possível entrar agora. Tente novamente em instantes.';
        }
    }
}
?>
<!doctype html>
<html lang="pt-BR">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Entrar — Gerenciador de Cartas</title>
    <link rel="stylesheet" href="/assets/styles.css">
</head>

<body class="login-page">
    <main class="login-panel">
        <header class="login-brand">
            <svg class="brand-icon" viewBox="0 0 48 48"
                fill="none" aria-hidden="true">
                <rect x="7" y="5" width="25" height="34" rx="4"
                    stroke="currentColor" stroke-width="2"
                    transform="rotate(-12 7 5)" />
                <rect x="15" y="9" width="25" height="34" rx="4"
                    fill="currentColor" />
            </svg>
            <p class="login-brand-name">Gerenciador de Cartas</p>
            <p>Painel administrativo</p>
        </header>

        <h1>Entrar no portal</h1>
        <p class="login-description">
            Use suas credenciais para acessar a gestão de cartas.
        </p>

        <?php if ($error !== ''): ?>
            <p class="error" role="alert"><?= escape($error) ?></p>
        <?php endif; ?>

        <form method="post" action="/login.php">
            <input type="hidden" name="csrf_token"
                value="<?= escape(csrf_token()) ?>">

            <label for="username">Usuário</label>
            <div class="login-input">
                <svg viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="1.8"
                    aria-hidden="true">
                    <circle cx="12" cy="8" r="4" />
                    <path d="M4 22v-3a8 8 0 0 1 16 0v3" />
                </svg>
                <input id="username" name="username"
                    value="<?= escape($username) ?>"
                    placeholder="Digite seu usuário"
                    autocomplete="username" maxlength="100" required>
            </div>

            <label for="password">Senha</label>
            <div class="login-input">
                <svg viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="1.8"
                    aria-hidden="true">
                    <rect x="4" y="10" width="16" height="12" rx="2" />
                    <path d="M8 10V6a4 4 0 0 1 8 0v4" />
                </svg>
                <input id="password" name="password" type="password"
                    placeholder="Digite sua senha"
                    autocomplete="current-password"
                    maxlength="4096" required>
            </div>

            <button type="submit">Entrar</button>
        </form>

        <footer class="login-footer">Acesso administrativo</footer>
    </main>
</body>

</html>