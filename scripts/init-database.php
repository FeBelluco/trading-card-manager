<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require dirname(__DIR__) . '/src/database.php';

try {
    $connection = database();

    // Os arquivos são SQL local versionado. DDL no MySQL faz commit implícito.
    foreach (['schema', 'seed'] as $file) {
        $sql = file_get_contents(dirname(__DIR__) . "/database/{$file}.sql");
        if ($sql === false) {
            throw new RuntimeException("Não foi possível ler {$file}.sql.");
        }
        $connection->exec($sql);
    }

    $statement = $connection->prepare('SELECT id FROM users WHERE username = :username');
    $statement->execute(['username' => 'admin']);

    if ($statement->fetchColumn() === false) {
        $statement = $connection->prepare(
            'INSERT INTO users (username, password_hash) VALUES (:username, :password_hash)'
        );
        $statement->execute([
            'username' => 'admin',
            'password_hash' => password_hash('LigaDev2026!', PASSWORD_DEFAULT),
        ]);
        echo 'Usuário de desenvolvimento criado. Credenciais no README.' . PHP_EOL;
    } else {
        echo 'Usuário admin existente preservado, incluindo a senha.' . PHP_EOL;
    }

    echo 'Schema e dados iniciais disponíveis.' . PHP_EOL;
} catch (Throwable $error) {
    fwrite(STDERR, 'Falha na inicialização: ' . $error->getMessage() . PHP_EOL);
    exit(1);
}
