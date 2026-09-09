<?php

declare(strict_types=1);

require dirname(__DIR__) . '/src/database.php';

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

try {
    foreach (['pdo_mysql', 'fileinfo', 'session'] as $extension) {
        if (!extension_loaded($extension)) {
            throw new RuntimeException("Extensão ausente: {$extension}");
        }
    }

    $result = database()->query('SELECT 1')->fetchColumn();

    if ((int) $result !== 1) {
        throw new RuntimeException('Resposta inesperada do banco.');
    }

    echo 'PHP ' . PHP_VERSION . ': extensões disponíveis e conexão MySQL OK.' . PHP_EOL;
} catch (Throwable $error) {
    fwrite(STDERR, 'Falha na verificação: ' . $error->getMessage() . PHP_EOL);
    exit(1);
}
