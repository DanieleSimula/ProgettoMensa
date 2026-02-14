<?php
$current_page = 'notizie';
require_once 'includes/session_check.php';

// Variabili per notizie
$messaggio_notizie = '';
$attiva = null;
$titolo = null;
$elenco_notizie = [];

// Gestione ricerca notizie
try {
    $query = "CALL get_notizie(:titolo, :attiva)";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':titolo', $titolo, PDO::PARAM_STR);
    $stmt->bindParam(':attiva', $attiva, PDO::PARAM_STR);
    if ($stmt->execute()) {
        $elenco_notizie = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($elenco_notizie) == 0) {
            $messaggio_notizie = '<div class="alert alert-warning">Nessuna notizia trovata con i criteri di ricerca specificati.</div>';
        } else {
            $messaggio_notizie = '<div class="alert alert-success">Trovate ' . count($elenco_notizie) . ' notizia/e.</div>';
        }
    } else {
        $messaggio_notizie = '<div class="alert alert-danger">Errore durante l\'esecuzione della ricerca.</div>';
    }
    $stmt->closeCursor();
    $stmt = null;
} catch (Exception $e) {
    $elenco_notizie = [];
}

require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>
<div class="col-md-9 col-lg-10 main-content">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col">
            <h1>Benvenuto <?php echo htmlspecialchars($user_role); ?></h1>
        </div>
    </div>

    <!-- Sezione Notizie -->
    <div class="row">
        <!-- Form Inserimento Notizia -->
        <div class="col-lg-5 mb-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Aggiungi Notizia</h5>
                </div>
                <div class="card-body">
                    <form id="form-notizia" method="POST" action="..\procedure\aggiungi_notizia.php"
                        style="display: inline;" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="titolo-notizia" class="form-label">Titolo *</label>
                            <input type="text" class="form-control" id="titolo-notizia" name="titolo"
                                maxlength="255" required>
                            <small class="form-text text-muted">Massimo 255 caratteri</small>
                        </div>

                        <div class="mb-3">
                            <label for="descrizione-notizia" class="form-label">Descrizione *</label>
                            <textarea class="form-control" id="descrizione-notizia" name="descrizione"
                                rows="3" maxlength="500" required></textarea>
                            <small class="form-text text-muted">Breve descrizione - Massimo 500 caratteri</small>
                        </div>

                        <div class="mb-3">
                            <label for="contenuto-notizia" class="form-label">Contenuto</label>

                            <!-- Toolbar per formattazione -->
                            <div class="btn-toolbar mb-2" role="toolbar">
                                <div class="btn-group btn-group-sm me-2" role="group">
                                    <button type="button" class="btn btn-outline-secondary"
                                        onclick="formatText('bold')" title="Grassetto">
                                        <i class="bi bi-type-bold"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary"
                                        onclick="formatText('italic')" title="Corsivo">
                                        <i class="bi bi-type-italic"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary"
                                        onclick="formatText('underline')" title="Sottolineato">
                                        <i class="bi bi-type-underline"></i>
                                    </button>
                                </div>
                                <div class="btn-group btn-group-sm me-2" role="group">
                                    <button type="button" class="btn btn-outline-secondary"
                                        onclick="formatText('insertUnorderedList')"
                                        title="Elenco puntato">
                                        <i class="bi bi-list-ul"></i>
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary"
                                        onclick="formatText('insertOrderedList')"
                                        title="Elenco numerato">
                                        <i class="bi bi-list-ol"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Area di testo editabile -->
                            <div id="editor-contenuto" contenteditable="true" class="form-control"
                                style="min-height: 200px; max-height: 400px; overflow-y: auto;">
                            </div>
                            <textarea id="contenuto-notizia" name="contenuto"
                                style="display: none;"></textarea>
                            <small class="form-text text-muted">Contenuto completo della notizia -
                                Massimo 65535 caratteri</small>
                        </div>

                        <div class="mb-3">
                            <label for="immagine-notizia" class="form-label">Immagine</label>
                            <input type="file" class="form-control" id="immagine-notizia"
                                name="immagine" accept="image/jpeg,image/png,image/jpg">
                            <small class="form-text text-muted">Formati: JPG, PNG, GIF - Massimo 5MB</small>
                        </div>

                        <input type="hidden" name="action" value="aggiungi_notizia">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-check-circle me-2"></i>Pubblica Notizia
                        </button>
                    </form>
                    <div id="message-notizia" class="mt-3"></div>
                </div>
            </div>
        </div>

        <!-- Elenco Notizie -->
        <div id="notizie-table-container" class="col-lg-7 table-responsive">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Lista Notizie</h5>
                </div>
                <?php if ($elenco_notizie && count($elenco_notizie) > 0): ?>
                    <table class="table table-striped table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Titolo notizia</th>
                                <th>Stato</th>
                                <th>Attiva/Disattiva</th>
                                <th>Elimina</th>
                            </tr>
                        </thead>
                        <tbody id="notizie-table-body">
                            <?php foreach ($elenco_notizie as $row1): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row1['titolo']); ?></td>
                                    <td><?php if ($row1['attiva'] == 1) echo "Attivata"; else echo "Disattivata"; ?></td>
                                    <td>
                                        <?php if ($row1['attiva'] == 0): ?>
                                            <form method="POST" action="..\procedure\toggle_notizia.php"
                                                style="display: inline;">
                                                <input type="hidden" name="id_notizia"
                                                    value="<?php echo $row1['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-success">Attiva</button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" action="..\procedure\toggle_notizia.php"
                                                style="display: inline;">
                                                <input type="hidden" name="id_notizia"
                                                    value="<?php echo $row1['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-warning">Disattiva</button>
                                            </form>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form method="POST" action="..\procedure\elimina_notizia.php"
                                            style="display: inline;">
                                            <input type="hidden" name="id_notizia"
                                                value="<?php echo $row1['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger">Elimina</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-center text-muted">Nessuna notizia trovata</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
<script src="assets/js/editor.js"></script>
