<?php

declare(strict_types=1);
require dirname(__DIR__) . '/src/auth.php';
require dirname(__DIR__) . '/src/cards.php';
start_session();
require_login();
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
try {
    $card = $id === false ? false : find_card($id);
    if ($card === false) {
        http_response_code(404);
        exit('Carta não encontrada. <a href="/">Voltar à lista</a>');
    }
    require dirname(__DIR__) . '/src/views/card-form.php';
} catch (Throwable $exception) {
    error_log((string) $exception);
    http_response_code(503);
    echo 'Não foi possível carregar a carta. <a href="/">Voltar à lista</a>';
}
