const status = document.querySelector('#cards-status');
const table = document.querySelector('#cards-table');
const rows = document.querySelector('#cards-rows');
const retry = document.querySelector('#cards-retry');
const login = document.querySelector('#cards-login');
const region = document.querySelector('#cards-region');
const dialog = document.querySelector('#delete-dialog');
const cancelDelete = document.querySelector('#delete-cancel');
const confirmDelete = document.querySelector('#delete-confirm');
const deleteStatus = document.querySelector('#delete-status');
const deleteLogin = document.querySelector('#delete-login');
const actionStatus = document.querySelector('#cards-action-status');
let selectedCard;
let deleting = false;

const pageUrl = new URL(window.location.href);

if (pageUrl.searchParams.has('updated') || pageUrl.searchParams.has('created')) {
    pageUrl.searchParams.delete('updated');
    pageUrl.searchParams.delete('created');

    window.history.replaceState(window.history.state, '', pageUrl.href);
}

function renderCards(cards) {
    const fragment = document.createDocumentFragment();

    for (const card of cards) {
        const row = document.createElement('tr');
        const imageCell = document.createElement('td');
        const image = document.createElement('img');
        image.src = `/image.php?id=${encodeURIComponent(card.id)}`;
        image.alt = `Imagem de ${card.name_en}`;
        image.className = 'card-thumbnail';
        image.loading = 'lazy';
        image.addEventListener('error', () => { imageCell.textContent = 'Imagem indisponível'; }, { once: true });
        imageCell.append(image);
        row.append(imageCell);
        const values = [card.name_en, card.name_pt || '—', card.card_game_name,
            card.edition_name, card.rarity_label];

        for (const value of values) {
            const cell = document.createElement('td');
            cell.textContent = value;
            row.append(cell);
        }

        const actions = document.createElement('td');
        const edit = document.createElement('a');
        edit.href = `/card-edit.php?id=${encodeURIComponent(card.id)}`;
        edit.textContent = 'Editar';
        edit.setAttribute('aria-label', `Editar ${card.name_en}`);
        const remove = document.createElement('button');
        remove.type = 'button';
        remove.textContent = 'Excluir';
        remove.className = 'danger';
        remove.setAttribute('aria-label', `Excluir ${card.name_en}`);
        remove.addEventListener('click', () => {
            selectedCard = card;
            document.querySelector('#delete-description').textContent = `Carta: ${card.name_en}`;
            deleteStatus.textContent = '';
            deleteLogin.hidden = true;
            confirmDelete.disabled = false;
            dialog.showModal();
            cancelDelete.focus();
        });
        actions.append(edit, document.createElement('br'), remove);
        row.append(actions);

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
cancelDelete.addEventListener('click', () => dialog.close());
dialog.addEventListener('cancel', (event) => { if (deleting) event.preventDefault(); });
confirmDelete.addEventListener('click', async () => {
    if (deleting || !selectedCard) return;
    deleting = true;
    cancelDelete.disabled = true;
    confirmDelete.disabled = true;
    deleteStatus.textContent = 'Excluindo carta…';
    let canRetry = true;
    try {
        const body = new FormData();
        body.set('csrf_token', document.querySelector('meta[name="csrf-token"]').content);
        body.set('action', 'delete');
        const response = await fetch(`/api/card.php?id=${encodeURIComponent(selectedCard.id)}`, { method: 'POST', body });
        if (response.status === 401) {
            deleteStatus.textContent = 'Sua sessão expirou. Entre novamente.';
            deleteLogin.hidden = false;
            canRetry = false;
            return;
        }
        const result = await response.json();
        if (!response.ok && response.status !== 404) {
            deleteStatus.textContent = result.error || 'Não foi possível excluir. Tente novamente.';
            return;
        }
        dialog.close();
        actionStatus.textContent = response.status === 404
            ? 'A carta já não existe. A lista foi atualizada.' : 'Carta excluída com sucesso.';
        await loadCards();
        region.focus();
    } catch {
        deleteStatus.textContent = 'Não foi possível confirmar a exclusão. Verifique sua conexão e tente novamente.';
    } finally {
        deleting = false;
        cancelDelete.disabled = false;
        confirmDelete.disabled = !canRetry;
    }
});
loadCards();
