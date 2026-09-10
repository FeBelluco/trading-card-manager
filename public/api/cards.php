<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/src/auth.php';
require dirname(__DIR__, 2) . '/src/cards.php';
start_session();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Sua sessão expirou. Entre novamente.']);
    exit;
}

// A consulta não altera a sessão: libera o bloqueio para outras requisições.
session_write_close();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido.']);
    exit;
}

try {
    echo json_encode(['cards' => list_cards()], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
} catch (Throwable $exception) {
    error_log((string) $exception);
    http_response_code(503);
    echo json_encode(['error' => 'Não foi possível carregar as cartas. Tente novamente.']);
}
