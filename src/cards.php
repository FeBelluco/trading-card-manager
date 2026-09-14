<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/uploads.php';

function list_cards(): array
{
    $cards = database()->query(
        'SELECT c.id, c.name_en, c.name_pt, c.rarity,
                e.id AS edition_id, e.name AS edition_name,
                g.id AS card_game_id, g.name AS card_game_name
         FROM cards c
         INNER JOIN editions e ON e.id = c.edition_id
         INNER JOIN card_games g ON g.id = e.card_game_id
         ORDER BY c.name_en, c.id'
    )->fetchAll();

    foreach ($cards as $index => $currentCard) {
        $cards[$index]['rarity_label'] =
            $currentCard['rarity'] . ' (' . strtoupper($currentCard['edition_id']) . ')';
    }

    return $cards;
}

function validate_card(array $input): array
{
    $errors = [];
    foreach (['name_en' => 255, 'name_pt' => 255, 'rarity' => 100,
        'card_game_id' => 16, 'edition_id' => 16] as $field => $limit) {
        $value = $input[$field] ?? '';
        $minimum = $field === 'name_pt' ? 0 : 1;
        if (!is_string($value) || preg_match('/\A.{' . $minimum . ',' . $limit . '}\z/us', trim($value)) !== 1) {
            $errors[$field] = $minimum === 0
                ? "Use no máximo {$limit} caracteres."
                : "Preencha este campo com até {$limit} caracteres.";
        }
    }
    if (!isset($errors['card_game_id']) && !isset($errors['edition_id'])) {
        $statement = database()->prepare('SELECT id FROM editions WHERE id = :edition AND card_game_id = :game');
        $statement->execute(['edition' => trim($input['edition_id']), 'game' => trim($input['card_game_id'])]);
        if ($statement->fetchColumn() === false) {
            $errors['edition_id'] = 'Selecione uma edição pertencente ao jogo escolhido.';
        }
    }
    return $errors;
}

function find_card(int $id): array|false
{
    $statement = database()->prepare(
        'SELECT c.*, e.card_game_id FROM cards c
         INNER JOIN editions e ON e.id = c.edition_id WHERE c.id = :id'
    );
    $statement->execute(['id' => $id]);
    return $statement->fetch();
}

function update_card(int $id, array $input, ?array $image): bool
{
    $connection = database();
    $newFilename = null;
    $connection->beginTransaction();
    try {
        $statement = $connection->prepare('SELECT image_path FROM cards WHERE id = :id FOR UPDATE');
        $statement->execute(['id' => $id]);
        $oldFilename = $statement->fetchColumn();
        if ($oldFilename === false) {
            $connection->rollBack();
            return false;
        }
        if ($image !== null) $newFilename = store_image($image);
        $statement = $connection->prepare(
            'UPDATE cards SET name_en = :name_en, name_pt = :name_pt,
             edition_id = :edition_id, rarity = :rarity, image_path = :image_path WHERE id = :id'
        );
        $statement->execute([
            'id' => $id,
            'name_en' => trim($input['name_en']),
            'name_pt' => trim($input['name_pt'] ?? '') === '' ? null : trim($input['name_pt']),
            'edition_id' => trim($input['edition_id']),
            'rarity' => trim($input['rarity']),
            'image_path' => $newFilename ?? $oldFilename,
        ]);
        $connection->commit();
    } catch (Throwable $exception) {
        if ($connection->inTransaction()) $connection->rollBack();
        if ($newFilename !== null) remove_image($newFilename);
        throw $exception;
    }
    // o arquivo antigo só é removido após o banco confirmar a alteração.
    if ($newFilename !== null) remove_image($oldFilename);
    return true;
}

function delete_card(int $id): bool
{
    $connection = database();
    $connection->beginTransaction();
    try {
        $statement = $connection->prepare('SELECT image_path FROM cards WHERE id = :id FOR UPDATE');
        $statement->execute(['id' => $id]);
        $filename = $statement->fetchColumn();
        if ($filename === false) {
            $connection->rollBack();
            return false;
        }
        $statement = $connection->prepare('DELETE FROM cards WHERE id = :id');
        $statement->execute(['id' => $id]);
        $connection->commit();
    } catch (Throwable $exception) {
        if ($connection->inTransaction()) $connection->rollBack();
        throw $exception;
    }
    remove_image($filename);
    return true;
}

function create_card(array $input, array $image): int
{
    $filename = store_image($image);
    try {
        $statement = database()->prepare(
            'INSERT INTO cards (name_en, name_pt, edition_id, image_path, rarity)
             VALUES (:name_en, :name_pt, :edition_id, :image_path, :rarity)'
        );
        $statement->execute([
            'name_en' => trim($input['name_en']),
            'name_pt' => trim($input['name_pt'] ?? '') === '' ? null : trim($input['name_pt']),
            'edition_id' => trim($input['edition_id']),
            'image_path' => $filename,
            'rarity' => trim($input['rarity']),
        ]);
        return (int) database()->lastInsertId();
    } catch (Throwable $exception) {
        //sistema de arquivos não participa da transação SQL.
        if (!unlink(uploads_directory() . '/' . $filename)) {
            error_log('Falha ao remover upload após erro de cadastro: ' . $filename);
        }
        throw $exception;
    }
}
