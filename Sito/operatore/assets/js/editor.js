// Funzione per formattare il testo nell'editor
function formatText(command) {
    document.execCommand(command, false, null);
    document.getElementById('editor-contenuto').focus();
}

// Sincronizza il contenuto dell'editor con il textarea nascosto prima dell'invio
document.getElementById('form-notizia')?.addEventListener('submit', function (e) {
    const editorContent = document.getElementById('editor-contenuto').innerHTML;
    document.getElementById('contenuto-notizia').value = editorContent;
});

// Limita la lunghezza del contenuto dell'editor
document.getElementById('editor-contenuto')?.addEventListener('input', function () {
    const text = this.innerText || this.textContent;
    if (text.length > 65535) {
        alert('Il contenuto ha raggiunto il limite massimo di 65535 caratteri');
        // Tronca il contenuto
        this.innerHTML = this.innerHTML.substring(0, 65535);
    }
});
