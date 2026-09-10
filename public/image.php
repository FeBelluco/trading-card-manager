<?php

declare(strict_types=1);

require dirname(__DIR__) . '/src/auth.php';
require dirname(__DIR__) . '/src/uploads.php';
start_session();
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}
session_write_close();
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    http_response_code(405);
    exit;
}
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($id === false) {
    http_response_code(404);
    exit;
}
try {
    $statement = database()->prepare('SELECT image_path FROM cards WHERE id = :id');
    $statement->execute(['id' => $id]);
    $filename = $statement->fetchColumn();
    if (!is_string($filename) || preg_match('/\A[a-f0-9]{32}\.(jpg|png|webp)\z/', $filename) !== 1) {
        http_response_code(404);
        exit;
    }
    $path = uploads_directory() . '/' . $filename;
    if (!is_file($path)) {
        http_response_code(404);
        exit;
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($path);
    if (!isset(IMAGE_TYPES[$mime])) {
        http_response_code(404);
        exit;
    }
    header('Content-Type: ' . $mime);
    header('X-Content-Type-Options: nosniff');
    header('Content-Disposition: inline; filename="' . $filename . '"');
    readfile($path);
} catch (Throwable $exception) {
    error_log((string) $exception);
    http_response_code(503);
}
