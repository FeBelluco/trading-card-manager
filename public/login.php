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
<body>
    <main class="login-panel">
        <p class="eyebrow">Gerenciador de Cartas</p>
        <h1>Entrar no portal</h1>
        <p>Use suas credenciais para acessar a gestão de cartas.</p>
        <?php if ($error !== ''): ?>
            <p class="error" role="alert"><?= escape($error) ?></p>
        <?php endif; ?>
        <form method="post" action="/login.php">
            <input type="hidden" name="csrf_token" value="<?= escape(csrf_token()) ?>">
            <label for="username">Usuário</label>
            <input id="username" name="username" value="<?= escape($username) ?>"
                autocomplete="username" maxlength="100" required>
            <label for="password">Senha</label>
            <input id="password" name="password" type="password"
                autocomplete="current-password" maxlength="4096" required>
            <button type="submit">Entrar</button>
        </form>
    </main>
</body>
</html>
