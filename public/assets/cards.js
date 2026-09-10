const status = document.querySelector('#cards-status');
const table = document.querySelector('#cards-table');
const rows = document.querySelector('#cards-rows');
const retry = document.querySelector('#cards-retry');
const login = document.querySelector('#cards-login');
const region = document.querySelector('#cards-region');

function renderCards(cards) {
    const fragment = document.createDocumentFragment();

    for (const card of cards) {
        const row = document.createElement('tr');
        const values = [card.name_en, card.name_pt || '—', card.card_game_name,
            card.edition_name, card.rarity];

        for (const value of values) {
            const cell = document.createElement('td');
            cell.textContent = value;
            row.append(cell);
        }

        fragment.append(row);
    }

    rows.replaceChildren(fragment);
    table.hidden = cards.length === 0;
    status.textContent = cards.length === 0
        ? 'Nenhuma carta cadastrada.'
        : `${cards.length} ${cards.length === 1 ? 'carta encontrada' : 'cartas encontradas'}.`;
}

async function loadCards() {
    retry.hidden = true;
    login.hidden = true;
    table.hidden = true;
    rows.replaceChildren();
    status.classList.remove('error');
    status.textContent = 'Carregando cartas…';
    region.setAttribute('aria-busy', 'true');

    try {
        const response = await fetch('/api/cards.php', { headers: { Accept: 'application/json' } });

        if (response.status === 401) {
            status.textContent = 'Sua sessão expirou. Entre novamente para visualizar as cartas.';
            login.hidden = false;
            return;
        }

        if (!response.ok) {
            throw new Error('Falha ao consultar cartas.');
        }

        const data = await response.json();
        if (!Array.isArray(data.cards)) {
            throw new Error('Resposta inválida.');
        }
        renderCards(data.cards);
    } catch {
        status.textContent = 'Não foi possível carregar as cartas. Verifique sua conexão e tente novamente.';
        status.classList.add('error');
        retry.hidden = false;
    } finally {
        region.setAttribute('aria-busy', 'false');
    }
}

retry.addEventListener('click', loadCards);
loadCards();
