<?php
$current_page = 'studenti';
require_once 'includes/session_check.php';

// Variabile per i risultati della ricerca
$risultati = null;
$messaggio = '';

// Gestione della ricerca studenti
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ricerca-campo']) && isset($_POST['ricerca-valore'])) {
    //recupero i parametri di ricerca studente e pulisco i dati dal form
    $campo = trim($_POST['ricerca-campo']);
    $valore = trim($_POST['ricerca-valore']);

    if (!empty($valore)) {
        try {
            // Preparo la chiamata alla stored procedure
            $query = "CALL ricerca_studenti(:campo, :valore)";
            $stmt = $conn->prepare($query);
            //bind dei parametri per la stored procedure
            $stmt->bindParam(':campo', $campo, PDO::PARAM_STR);
            $stmt->bindParam(':valore', $valore, PDO::PARAM_STR);

            // Eseguo la query
            if ($stmt->execute()) {
                // Recupero tutti i risultati
                $risultati = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Controllo se ci sono risultati
                if (count($risultati) == 0) {
                    $messaggio = '<div class="alert alert-warning">Nessuno studente trovato con i criteri di ricerca specificati.</div>';
                } else {
                    $messaggio = '<div class="alert alert-success">Trovati ' . count($risultati) . ' studente/i.</div>';
                }
            } else {
                $messaggio = '<div class="alert alert-danger">Errore durante l\'esecuzione della ricerca.</div>';
            }

            // Chiudo il cursore per permettere altre query
            $stmt->closeCursor();

        } catch (PDOException $e) {
            $messaggio = '<div class="alert alert-danger">Errore di connessione: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    } else {
        $messaggio = '<div class="alert alert-warning">Inserisci un valore da cercare.</div>';
    }
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

    <!-- Sezione Studenti -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-people-fill me-2"></i>Gestione Studenti</h5>
        </div>
        <div class="card-body">
            <!-- Barra di ricerca -->
            <form id="form-ricerca-studente" method="POST" action="" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="ricerca-campo" class="form-label">Cerca per:</label>
                        <select class="form-select" id="ricerca-campo" name="ricerca-campo">
                            <option value="cf">CF</option>
                            <option value="nomeutente">Nome Utente</option>
                            <option value="email">Email</option>
                            <option value="nome">Nome</option>
                            <option value="cognome">Cognome</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="ricerca-valore" class="form-label">Valore:</label>
                        <input type="text" class="form-control" id="ricerca-valore"
                            name="ricerca-valore" placeholder="Inserisci il valore da cercare">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-2"></i>Cerca
                        </button>
                    </div>
                </div>
            </form>

            <!-- Messaggio -->
            <div id="message-studenti" class="mb-3">
                <?php if (!empty($messaggio)) echo $messaggio; ?>
            </div>

            <!-- Tabella risultati -->
            <?php if ($risultati && count($risultati) > 0): ?>
                <div id="studenti-table-container" class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>CF</th>
                                <th>Nome Utente</th>
                                <th>Email</th>
                                <th>Nome</th>
                                <th>Cognome</th>
                                <th>Sesso</th>
                                <th>Data Nascita</th>
                                <th>Città</th>
                                <th>ISEE</th>
                                <th>Pasti</th>
                                <th>Azioni</th>
                            </tr>
                        </thead>
                        <tbody id="studenti-table-body">
                            <?php foreach ($risultati as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['cf']); ?></td>
                                    <td><?php echo htmlspecialchars($row['nomeutente']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['nome']); ?></td>
                                    <td><?php echo htmlspecialchars($row['cognome']); ?></td>
                                    <td><?php echo htmlspecialchars($row['sesso']); ?></td>
                                    <td><?php echo htmlspecialchars($row['datanascita']); ?></td>
                                    <td><?php echo htmlspecialchars($row['citta']); ?></td>
                                    <td><?php echo htmlspecialchars($row['fascia']); ?></td>
                                    <td><?php echo htmlspecialchars($row['pasti']); ?></td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal"
                                            data-bs-target="#modalStudente<?php echo $row['cf']; ?>">Modifica</button>

                                        <!-- Modal per gestione pasti -->
                                        <div class="modal fade" id="modalStudente<?php echo $row['cf']; ?>"
                                            tabindex="-1" role="dialog"
                                            aria-labelledby="modalStudenteLabel<?php echo $row['cf']; ?>"
                                            aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="exampleModalLabel">Gestione pasti di
                                                            <?php echo $row['nome'], " ", $row['cognome']; ?>
                                                        </h5>
                                                        <button type="button" class="close"
                                                            data-bs-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form method="POST"
                                                            action="..\procedure\aggiungi_pasto.php"
                                                            style="display: inline;">
                                                            <div class="form-group">
                                                                <label for="recipient-name"
                                                                    class="col-form-label"></label>
                                                                <h5 id="pasti">Numero pasti:
                                                                    <?php echo $row['pasti']; ?>
                                                                </h5>
                                                                <label for="recipient-name"
                                                                    class="col-form-label"></label>
                                                                <input type="number" name="quantita"
                                                                    class="form-control"
                                                                    id="quantita-<?php echo $row['cf']; ?>"
                                                                    required>
                                                            </div>

                                                            <div class="modal-footer">
                                                                <!-- Bottone Aggiungi -->
                                                                <input type="hidden" name="cf"
                                                                    value="<?php echo $row['cf']; ?>">
                                                                <input type="hidden" name="fascia"
                                                                    value="<?php echo $row['fascia']; ?>">
                                                                <button type="submit"
                                                                    class="btn btn-success">Aggiungi pasto</button>
                                                        </form>

                                                        <!-- Bottone Rimuovi -->
                                                        <form method="POST"
                                                            action="..\procedure\rimuovi_pasto.php"
                                                            style="display: inline;">
                                                            <input type="hidden" name="cf"
                                                                value="<?php echo $row['cf']; ?>">
                                                            <button type="submit"
                                                                class="btn btn-primary">Rimuovi pasto</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
