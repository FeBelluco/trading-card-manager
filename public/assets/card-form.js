const form = document.querySelector('#card-form');
const fields = document.querySelector('#card-fields');
const game = document.querySelector('#card_game_id');
const edition = document.querySelector('#edition_id');
const editionStatus = document.querySelector('#edition-status');
const retry = document.querySelector('#editions-retry');
const save = document.querySelector('#save-card');
const status = document.querySelector('#form-status');
const login = document.querySelector('#form-login');
const image = document.querySelector('#image');
const preview = document.querySelector('#image-preview');
const previewLabel = document.querySelector('#image-preview-label');
const editing = form.dataset.editing === 'true';
const saveLabel = editing ? 'Salvar alterações' : 'Cadastrar carta';
let initialEdition = form.dataset.edition;
let editionRequest;
let previewUrl;
let saving = false;

function updateSave() {
    save.disabled = saving || edition.disabled || edition.value === '';
}

function expiredSession() {
    status.textContent = 'Sua sessão expirou. Entre novamente antes de salvar.';
    login.hidden = false;
}

async function loadEditions() {
    editionRequest?.abort();
    const request = new AbortController();
    editionRequest = request;
    edition.replaceChildren(new Option('Selecione um jogo primeiro', ''));
    edition.disabled = true;
    edition.removeAttribute('aria-invalid');
    document.querySelector('#edition_id-error').textContent = '';
    retry.hidden = true;
    editionStatus.textContent = '';
    updateSave();
    if (!game.value) return;

    edition.replaceChildren(new Option('Carregando edições…', ''));
    editionStatus.textContent = 'Carregando edições…';
    try {
        const response = await fetch(`/api/editions.php?game=${encodeURIComponent(game.value)}`, {
            signal: request.signal, headers: { Accept: 'application/json' },
        });
        if (request !== editionRequest) return;
        if (response.status === 401) {
            expiredSession();
            editionStatus.textContent = 'Entre novamente para carregar as edições.';
            return;
        }
        if (!response.ok) throw new Error('Falha nas edições.');
        const data = await response.json();
        if (request !== editionRequest) return;
        if (!Array.isArray(data.editions)) throw new Error('Resposta inválida.');
        edition.replaceChildren(new Option('Selecione uma edição', ''));
        for (const item of data.editions) edition.add(new Option(item.name, item.id));
        if (initialEdition) {
            edition.value = initialEdition;
            initialEdition = '';
        }
        edition.disabled = data.editions.length === 0;
        editionStatus.textContent = data.editions.length ? 'Edições carregadas.' : 'Nenhuma edição disponível para este jogo.';
    } catch (error) {
        if (request !== editionRequest || error.name === 'AbortError') return;
        edition.replaceChildren(new Option('Não foi possível carregar', ''));
        editionStatus.textContent = 'Falha ao carregar as edições. Tente novamente.';
        retry.hidden = false;
    } finally {
        if (request === editionRequest) updateSave();
    }
}

function clearErrors() {
    for (const element of form.querySelectorAll('[aria-invalid]')) element.removeAttribute('aria-invalid');
    for (const element of form.querySelectorAll('.field-error')) element.textContent = '';
}

function showErrors(errors) {
    let first;
    for (const [name, message] of Object.entries(errors)) {
        const input = form.elements.namedItem(name);
        const label = document.getElementById(`${name}-error`);
        if (!input || !label) continue;
        input.setAttribute('aria-invalid', 'true');
        label.textContent = message;
        first ??= input;
    }
    first?.focus();
}

image.addEventListener('change', () => {
    if (previewUrl) URL.revokeObjectURL(previewUrl);
    preview.removeAttribute('src');
    preview.hidden = true;
    previewLabel.hidden = true;
    image.setCustomValidity('');
    image.removeAttribute('aria-invalid');
    document.querySelector('#image-error').textContent = '';
    const file = image.files[0];
    if (!file) return;
    if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type) || file.size > 5 * 1024 * 1024 || file.size === 0) {
        image.setCustomValidity('Selecione uma imagem JPEG, PNG ou WebP de até 5 MB.');
        showErrors({ image: image.validationMessage });
        return;
    }
    previewUrl = URL.createObjectURL(file);
    preview.src = previewUrl;
    preview.hidden = false;
    previewLabel.hidden = false;
});

form.addEventListener('submit', async (event) => {
    event.preventDefault();
    if (saving || edition.disabled || !edition.value || !form.reportValidity()) return;
    clearErrors();
    login.hidden = true;
    status.textContent = 'Salvando carta…';
    const data = new FormData(form);
    saving = true;
    fields.disabled = true;
    save.textContent = 'Salvando…';
    updateSave();
    let errors = {};
    try {
        const response = await fetch(form.getAttribute('action'), { method: 'POST', body: data, headers: { Accept: 'application/json' } });
        if (response.status === 401) { expiredSession(); return; }
        const result = await response.json();
        if (!response.ok) {
            status.textContent = result.error || 'Não foi possível salvar a carta.';
            errors = result.fields || {};
            return;
        }
        window.location.assign(editing ? '/?updated=1' : '/?created=1');
    } catch {
        status.textContent = 'Não foi possível confirmar a gravação. Verifique a lista antes de tentar novamente.';
    } finally {
        saving = false;
        fields.disabled = false;
        save.textContent = saveLabel;
        updateSave();
        showErrors(errors);
    }
});

game.addEventListener('change', () => { initialEdition = ''; loadEditions(); });
retry.addEventListener('click', loadEditions);
edition.addEventListener('change', updateSave);
// Também trata valores restaurados pelo navegador ao voltar para o formulário.
if (game.value) loadEditions();
