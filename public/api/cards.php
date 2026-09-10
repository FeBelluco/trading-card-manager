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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ((int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 6 * 1024 * 1024) {
        http_response_code(413);
        echo json_encode(['error' => 'Envio muito grande. Use uma imagem de até 5 MB.']);
        exit;
    }
    if (!valid_csrf()) {
        http_response_code(403);
        echo json_encode(['error' => 'Solicitação inválida ou sessão expirada. Recarregue a página.']);
        exit;
    }
}

// Libera o bloqueio da sessão antes de consultar o banco ou salvar arquivos.
session_write_close();

if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'], true)) {
    header('Allow: GET, POST');
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido.']);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $errors = validate_card($_POST);
        $imageError = validate_image($_FILES['image'] ?? null);
        if ($imageError !== null) {
            $errors['image'] = $imageError;
        }
        if ($errors !== []) {
            http_response_code(422);
            echo json_encode(['error' => 'Confira os campos indicados.', 'fields' => $errors]);
            exit;
        }
        $id = create_card($_POST, $_FILES['image']);
        http_response_code(201);
        echo json_encode(['id' => $id]);
        exit;
    }
    echo json_encode(['cards' => list_cards()], JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
} catch (Throwable $exception) {
    error_log((string) $exception);
    http_response_code(503);
    echo json_encode(['error' => 'Não foi possível concluir a operação. Tente novamente.']);
}
