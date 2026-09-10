<?php

declare(strict_types=1);

require dirname(__DIR__, 2) . '/src/auth.php';
start_session();
header('Content-Type: application/json; charset=utf-8');
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Sua sessão expirou. Entre novamente.']);
    exit;
}
session_write_close();
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido.']);
    exit;
}
$game = $_GET['game'] ?? null;
if (!is_string($game) || !in_array($game, ['magic', 'pokemon', 'yugioh'], true)) {
    http_response_code(422);
    echo json_encode(['error' => 'Selecione um jogo válido.']);
    exit;
}
try {
    $statement = database()->prepare('SELECT id, name FROM editions WHERE card_game_id = :game ORDER BY name, id');
    $statement->execute(['game' => $game]);
    echo json_encode(['editions' => $statement->fetchAll()], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
} catch (Throwable $exception) {
    error_log((string) $exception);
    http_response_code(503);
    echo json_encode(['error' => 'Não foi possível carregar as edições.']);
}
