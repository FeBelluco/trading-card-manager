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
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido.']);
    exit;
}
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
session_write_close();
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$action = $_POST['action'] ?? '';
if ($id === false || !in_array($action, ['update', 'delete'], true)) {
    http_response_code(422);
    echo json_encode(['error' => 'Carta ou ação inválida.']);
    exit;
}
try {
    if (find_card($id) === false) {
        http_response_code(404);
        echo json_encode(['error' => 'Esta carta não existe mais. Volte à lista.']);
        exit;
    }
    if ($action === 'delete') {
        $success = delete_card($id);
    } else {
        $errors = validate_card($_POST);
        $image = $_FILES['image'] ?? null;
        // Sem novo arquivo, preserva a imagem atual.
        if (is_array($image) && ($image['error'] ?? null) === UPLOAD_ERR_NO_FILE) $image = null;
        if ($image !== null) {
            $imageError = validate_image($image);
            if ($imageError !== null) $errors['image'] = $imageError;
        }
        if ($errors !== []) {
            http_response_code(422);
            echo json_encode(['error' => 'Confira os campos indicados.', 'fields' => $errors]);
            exit;
        }
        $success = update_card($id, $_POST, $image);
    }
    if (!$success) {
        http_response_code(404);
        echo json_encode(['error' => 'Esta carta não existe mais. Volte à lista.']);
        exit;
    }
    echo json_encode(['id' => $id]);
} catch (Throwable $exception) {
    error_log((string) $exception);
    http_response_code(503);
    echo json_encode(['error' => 'Não foi possível concluir a operação. Tente novamente.']);
}
