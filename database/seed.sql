-- IDs e nomes reproduzem o JSON do PDF. Reexecutar preserva registros existentes.
INSERT INTO card_games (id, name) VALUES
    ('magic', 'Magic: The Gathering'),
    ('pokemon', 'Pokémon'),
    ('yugioh', 'Yu-Gi-Oh!')
ON DUPLICATE KEY UPDATE id = card_games.id;

INSERT INTO editions (id, card_game_id, name) VALUES
    ('dom', 'magic', 'Dominaria'),
    ('war', 'magic', 'War of the Spark'),
    ('eld', 'magic', 'Throne of Eldraine'),
    ('hob', 'magic', 'The Hobbit'),
    ('msh', 'magic', 'Marvel Super Heroes'),
    ('base1', 'pokemon', 'Base Set'),
    ('swsh1', 'pokemon', 'Sword & Shield'),
    ('sv1', 'pokemon', 'Scarlet & Violet'),
    ('30c', 'pokemon', '30th Celebration'),
    ('cri', 'pokemon', 'Chaos Rising'),
    ('lob', 'yugioh', 'Legend of Blue Eyes White Dragon'),
    ('mrd', 'yugioh', 'Metal Raiders'),
    ('sdy', 'yugioh', 'Starter Deck: Yugi'),
    ('rotd', 'yugioh', 'Rise of the Duelist'),
    ('blzd', 'yugioh', 'Blazing Dominion')
ON DUPLICATE KEY UPDATE id = editions.id;
