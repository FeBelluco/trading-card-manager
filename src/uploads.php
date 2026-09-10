<?php

declare(strict_types=1);

const IMAGE_MAX_BYTES = 5 * 1024 * 1024;
const IMAGE_TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

function uploads_directory(): string
{
    return dirname(__DIR__) . '/storage/uploads';
}

function validate_image(mixed $file): ?string
{
    if (!is_array($file) || !isset($file['error']) || is_array($file['error'])) {
        return 'Selecione uma imagem.';
    }
    if (in_array($file['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
        return 'A imagem deve ter no máximo 5 MB.';
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return 'Não foi possível receber a imagem. Selecione o arquivo novamente.';
    }
    $path = $file['tmp_name'] ?? null;
    if (!is_string($path) || !is_uploaded_file($path)) {
        return 'Upload de imagem inválido.';
    }
    $size = filesize($path);
    if ($size === false || $size === 0 || $size > IMAGE_MAX_BYTES) {
        return 'A imagem deve ter entre 1 byte e 5 MB.';
    }
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($path);
    $dimensions = @getimagesize($path);
    if (!isset(IMAGE_TYPES[$mime]) || $dimensions === false || ($dimensions['mime'] ?? '') !== $mime) {
        return 'Envie uma imagem JPEG, PNG ou WebP válida.';
    }
    return null;
}

function store_image(array $file): string
{
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $filename = bin2hex(random_bytes(16)) . '.' . IMAGE_TYPES[$mime];
    if (!move_uploaded_file($file['tmp_name'], uploads_directory() . '/' . $filename)) {
        throw new RuntimeException('Não foi possível armazenar a imagem.');
    }
    return $filename;
}
