<?php

declare(strict_types=1);

require_once __DIR__ . '/database.php';

function list_cards(): array
{
    return database()->query(
        'SELECT c.id, c.name_en, c.name_pt, c.rarity,
                e.id AS edition_id, e.name AS edition_name,
                g.id AS card_game_id, g.name AS card_game_name
         FROM cards c
         INNER JOIN editions e ON e.id = c.edition_id
         INNER JOIN card_games g ON g.id = e.card_game_id
         ORDER BY c.name_en, c.id'
    )->fetchAll();
}
