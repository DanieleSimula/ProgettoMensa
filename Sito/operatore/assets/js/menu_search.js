function initSearch(category) {
    const input = document.getElementById('search-' + category);
    const resultsBox = document.getElementById('results-' + category);
    const selectedBox = document.getElementById('selected-' + category);

    let selected = new Map();

    input.addEventListener('input', function () {
        const q = this.value.toLowerCase();
        resultsBox.innerHTML = '';

        if (q.length < 1) return;

        const matches = piatti.filter(p =>
            p.nome.toLowerCase().includes(q) &&
            !selected.has(p.id)
        );

        matches.slice(0, 20).forEach(p => {
            const item = document.createElement('button');
            item.type = "button";
            item.className = "list-group-item list-group-item-action";
            item.textContent = p.nome;

            item.addEventListener('click', () => {
                addItem(p);
            });

            resultsBox.appendChild(item);
        });
    });

    function addItem(p) {
        selected.set(p.id, p.nome);

        const badge = document.createElement('span');
        badge.className = "badge bg-primary me-1 mb-1";
        badge.style.cursor = "pointer";

        badge.innerHTML = `
            ${p.nome}
            <input type="hidden" name="${category}[]" value="${p.id}">
            <span class="ms-1">✕</span>`;

        badge.addEventListener('click', () => {
            selected.delete(p.id);
            badge.remove();
        });

        selectedBox.appendChild(badge);

        input.value = '';
        resultsBox.innerHTML = '';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    initSearch('piatti');
});
