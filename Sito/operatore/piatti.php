<?php
$current_page = 'piatti';
require_once 'includes/session_check.php';
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Inizializzazione variabili
$elenco_piatti = [];
$messaggio_piatti = "";
// Questa variabile ci serve per dire al Javascript quale scheda aprire al caricamento
$active_section = '';

// Gestione Ricerca Piatti
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ricerca_piatti') {

    // Manteniamo aperta la sezione piatti dopo il ricaricamento
    $active_section = 'piatti';

    // Recupero dati dal form
    $campo = $_POST['campo_ricerca_piatto'] ?? '';
    $valore = $_POST['ricerca_piatto'] ?? '';

    $id_piatto = null;
    $nome_piatto = null;

    // Imposto i parametri in base alla scelta della select
    if ($campo === 'id') {
        $id_piatto = !empty($valore) ? intval($valore) : null;
    } elseif ($campo === 'nome') {
        $nome_piatto = !empty($valore) ? $valore : null;
    }

    try {
        $query = "CALL ricerca_piatti(:id, :nome)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':id', $id_piatto, PDO::PARAM_INT);
        $stmt->bindParam(':nome', $nome_piatto, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $elenco_piatti = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($elenco_piatti) == 0) {
                $messaggio_piatti = '<div class="alert alert-warning">Nessun piatto trovato.</div>';
            } else {
                $messaggio_piatti = '<div class="alert alert-success">Trovati ' . count($elenco_piatti) . ' piatti.</div>';
            }
        } else {
            $messaggio_piatti = '<div class="alert alert-danger">Errore esecuzione ricerca.</div>';
        }
        $stmt->closeCursor();
        $stmt = null;
    } catch (Exception $e) {
        $messaggio_piatti = '<div class="alert alert-danger">Errore: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}


?>




<div class="col-md-9 col-lg-10 main-content">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col">
            <h1>Benvenuto <?php echo htmlspecialchars($user_role); ?></h1>
        </div>
    </div>

    <!-- Gestione Piatti -->
    <div class="row">
        <!-- Form Inserimento Piatto -->
        <div class="col-lg-5 mb-4">
            <div class="card">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0"><i class="bi bi-card-list me-2"></i>Gestione Piatti</h5>
                </div>
                <div class="card-body">
                    <form id="form-piatti" method="POST" style="display: inline;" action="..\procedure\aggiungi_piatto.php" >
                        <div class="mb-3">
                            <label for="nome-piatto" class="form-label">Nome Piatto *</label>
                            <input type="text" class="form-control" id="nome-piatto" name="nome_piatto" maxlenght="20"
                                required>
                            <small class="form-text text-muted">Massimo 20 caratteri</small>
                        </div>
                        <div class="mb-3">
                            <label for="descrizione-piatto" class="form-label">Descrizione Piatto</label>
                            <textarea class="form-control" id="descrizione-piatto" name="descrizione_piatto" rows="3"
                                maxlength="255"></textarea>
                            <small class="form-text text-muted">Massimo 255 caratteri</small>
                        </div>
                        <input type="hidden" name="action" value="aggiungi_piatto">
                        <button type="submit" class="btn btn-warning w-100">
                            <i class="bi bi-check-circle me-2"></i>Aggiungi Piatto
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-5 mb-4">
            <div class="card">
                <div class="card-header bg-warning text-white">
                    <h5 class="mb-0"><i class="bi bi-card-list me-2"></i>Ricerca Piatti</h5>
                </div>
                <div class="card-body">
                    <form id="form-ricerca-piatti" method="POST" action="" style="display: inline;">
                        <input type="hidden" name="action" value="ricerca_piatti">
                        <div class="mb-3">
                            <label for="ricerca-campo-piatto" class="form-label">Cerca per</label>
                            <select class="form-select" id="ricerca-campo-piatto" name="campo_ricerca_piatto" required>
                                <option value="">Seleziona campo</option>
                                <option value="nome">Nome</option>
                                <option value="id">id</option>
                            </select>
                        </div>
                        <input type="text" class="form-control" id="ricerca-campo-piatto" name="ricerca_piatto"
                            maxlength="20">
                        <small class="form-text text-muted">Inserisci il nome del piatto da cercare</small>
                </div>
                <button type="submit" class="btn btn-info w-100">
                    <i class="bi bi-search me-2"></i>Cerca Piatti
                </button>
                </form>
            </div>
            <div class="card-body">
                <?php if (isset($elenco_piatti) && count($elenco_piatti) > 0): ?>
                    <table class="table table-striped table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Nome Piatto</th>
                                <th>Descrizione</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($elenco_piatti as $piatto): ?>
                                <tr>
                                    <td>
                                        <?php echo htmlspecialchars($piatto['id']); ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($piatto['nome']); ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($piatto['descrizione']); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php elseif (isset($ricerca_piatto)): ?>
                    <p class="text-center text-muted">Nessun piatto trovato</p>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>
<script src="assets/js/plate_search.js"></script>
<?php
require_once 'includes/footer.php';
?>