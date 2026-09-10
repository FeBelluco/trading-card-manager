<?php

declare(strict_types=1);

require dirname(__DIR__) . '/src/auth.php';
start_session();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Método não permitido.');
}

require_login();

if (!valid_csrf()) {
    http_response_code(403);
    exit('Solicitação inválida. Volte ao portal e tente novamente.');
}

logout();
header('Location: /login.php', true, 303);
exit;
